require('dotenv').config();
const express = require('express');
const bodyParser = require('body-parser');
const axios = require('axios');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware to parse incoming JSON bodies
// This is necessary to properly read the data WhatsApp sends to the webhook
app.use(bodyParser.json());

// Extract credentials from environment variables
const WHATSAPP_TOKEN = process.env.WHATSAPP_TOKEN;
const VERIFY_TOKEN = process.env.VERIFY_TOKEN;
const PHONE_NUMBER_ID = process.env.PHONE_NUMBER_ID;
// WABA_ID is also setup in .env as per requirements, even if it's not strictly needed for this endpoint

// GET endpoint to verify the webhook
// This is required by the WhatsApp Cloud API during the webhook setup in the Meta App Dashboard
app.get('/webhook', (req, res) => {
    // Parse the query params from the verify request
    const mode = req.query['hub.mode'];
    const token = req.query['hub.verify_token'];
    const challenge = req.query['hub.challenge'];

    // Check if a mode and token were sent
    if (mode && token) {
        // Check if the mode is 'subscribe' and the token matches the VERIFY_TOKEN in .env
        if (mode === 'subscribe' && token === VERIFY_TOKEN) {
            console.log('WEBHOOK_VERIFIED');
            // Respond with the challenge token from the request
            res.status(200).send(challenge);
        } else {
            // Respond with '403 Forbidden' if verify tokens do not match
            res.sendStatus(403);
        }
    } else {
        // If mode or token is missing, reject the request
        res.sendStatus(400);
    }
});

// POST endpoint to handle incoming WhatsApp messages
app.post('/webhook', async (req, res) => {
    // Return early with a 200 OK so WhatsApp doesn't retry delivering the payload
    res.sendStatus(200);

    // Parse the request body
    const body = req.body;

    // Check if this is an event from a WhatsApp API
    if (body.object === 'whatsapp_business_account') {
        if (
            body.entry &&
            body.entry[0].changes &&
            body.entry[0].changes[0] &&
            body.entry[0].changes[0].value.messages &&
            body.entry[0].changes[0].value.messages[0]
        ) {
            const value = body.entry[0].changes[0].value;
            const message = value.messages[0];
            const from = message.from; // The user's WhatsApp number

            // Log all incoming messages to the console
            console.log(`Incoming message from ${from}:`, JSON.stringify(message, null, 2));

            // ── Handle plain text messages ──
            if (message.type === 'text') {
                const incomingText = message.text.body.toLowerCase().trim();

                if (incomingText === 'menu') {
                    await sendInteractiveMenu(from);
                } else {
                    await sendTextMessage(from, 'Bonjour! Répondez avec "menu" pour voir les options disponibles.');
                }

            // ── Handle interactive button replies (customer tapped a confirmation button) ──
            } else if (message.type === 'interactive' && message.interactive.type === 'button_reply') {
                const buttonId = message.interactive.button_reply.id;
                // Button IDs are formatted as: "confirm-ORDER-NAME" or "cancel-ORDER-NAME"
                const parts = buttonId.split('-');
                const action = parts[0];           // "confirm" or "cancel"
                const orderNumber = parts[1] || 'N/A';
                const customerName = parts.slice(2).join(' ') || 'Client';

                // Owner phone number to notify (without + prefix)
                const OWNER_PHONE = '212704969534';

                if (action === 'confirm') {
                    // 1. Confirm with the customer
                    await sendTextMessage(from, `✅ Parfait! Votre commande *#${orderNumber}* est confirmée.\nNous vous contacterons bientôt pour la livraison. Merci!`);
                    // 2. Notify the owner
                    await sendTextMessage(OWNER_PHONE, `✅ *Commande CONFIRMÉE*\n\n*Commande:* #${orderNumber}\n*Client:* ${customerName}\n*Téléphone:* +${from}`);

                } else if (action === 'cancel') {
                    // 1. Acknowledge the customer
                    await sendTextMessage(from, `❌ D'accord, votre commande *#${orderNumber}* a été annulée.\nN'hésitez pas à nous recontacter si vous changez d'avis!`);
                    // 2. Notify the owner
                    await sendTextMessage(OWNER_PHONE, `❌ *Commande ANNULÉE*\n\n*Commande:* #${orderNumber}\n*Client:* ${customerName}\n*Téléphone:* +${from}`);
                }
            }
        }
    }
});


// Helper function to send an interactive list message
async function sendInteractiveMenu(to) {
    // Defines an interactive list message structure
    // Requires at least 3 options and 2 sections as per user request
    const data = {
        messaging_product: 'whatsapp',
        to: to, // Handles multiple users individually based on who sent the message
        type: 'interactive',
        interactive: {
            type: 'list',
            header: {
                type: 'text',
                text: 'Main Menu'
            },
            body: {
                text: 'Please select an option from the menu below:'
            },
            footer: {
                text: 'Powered by WhatsApp Cloud API Bot'
            },
            action: {
                button: 'View Options',
                sections: [
                    {
                        title: 'Services',
                        rows: [
                            {
                                id: 'service_1',
                                title: 'Web Development',
                                description: 'Build a custom website.'
                            },
                            {
                                id: 'service_2',
                                title: 'App Development',
                                description: 'Build a mobile app.'
                            }
                        ]
                    },
                    {
                        title: 'Support',
                        rows: [
                            {
                                id: 'support_1',
                                title: 'Contact Support',
                                description: 'Get in touch with our team.'
                            },
                            {
                                id: 'support_2',
                                title: 'FAQs',
                                description: 'Check frequently asked questions.'
                            }
                        ]
                    }
                ]
            }
        }
    };

    try {
        // We use v17.0 but any newer version like v18.0 or v19.0 works as well
        const response = await axios({
            method: 'POST',
            url: `https://graph.facebook.com/v19.0/${PHONE_NUMBER_ID}/messages`,
            data: data,
            headers: {
                'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });
        console.log(`Successfully sent interactive menu to ${to}. Message ID: ${response.data.messages[0].id}`);
    } catch (error) {
        console.error('Error sending interactive menu:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
    }
}

// Helper function to send a standard text message
async function sendTextMessage(to, text) {
    const data = {
        messaging_product: 'whatsapp',
        to: to,
        type: 'text',
        text: { body: text }
    };

    try {
        const response = await axios({
            method: 'POST',
            url: `https://graph.facebook.com/v19.0/${PHONE_NUMBER_ID}/messages`,
            data: data,
            headers: {
                'Authorization': `Bearer ${WHATSAPP_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });
        console.log(`Successfully sent text message to ${to}. Message ID: ${response.data.messages[0].id}`);
    } catch (error) {
        console.error('Error sending text message:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
    }
}

// Import the external API router
const confirmationRoute = require('./api/Confirmation');
// Use the router for endpoints under /api
app.use('/api', confirmationRoute);

// Serve the documentation HTML file at /api/docs
const path = require('path');
app.get('/api/docs', (req, res) => {
    res.sendFile(path.join(__dirname, 'api', 'doc.html'));
});

// Start the Express server
app.listen(PORT, () => {
    console.log(`WhatsApp Bot server is listening on port ${PORT}`);
    console.log(`Webhook endpoint is available at http://localhost:${PORT}/webhook`);
});
