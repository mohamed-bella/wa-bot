const express = require('express');
const router = express.Router();
const axios = require('axios');

// Route: POST /api/confirmation
// Called by your website when a customer places an order.
// Sends the customer an interactive WhatsApp message with 2 buttons to confirm or cancel.
router.post('/confirmation', async (req, res) => {
    const { phone, customerName, orderNumber } = req.body;

    // Basic validation
    if (!phone) {
        return res.status(400).json({ success: false, error: 'Phone number is required' });
    }

    const name = customerName || 'Client';
    const order = orderNumber || 'N/A';

    // Build an interactive button message (WhatsApp Cloud API format)
    // Button IDs encode the order number and name so the webhook handler can parse them
    const data = {
        messaging_product: 'whatsapp',
        to: phone,
        type: 'interactive',
        interactive: {
            type: 'button',
            body: {
                text: `🛒 *Nouvelle Commande #${order}*\n\nBonjour *${name}*,\n\nNous avons bien reçu votre commande. Veuillez confirmer votre commande ci-dessous.`
            },
            footer: {
                text: 'Merci de votre confiance ❤️'
            },
            action: {
                buttons: [
                    {
                        type: 'reply',
                        reply: {
                            // ID encodes orderNumber and customerName so the webhook can read it
                            id: `confirm_${order}_${name}`,
                            title: '✅ Je confirme'
                        }
                    },
                    {
                        type: 'reply',
                        reply: {
                            id: `cancel_${order}_${name}`,
                            title: '❌ Non, Désolé'
                        }
                    }
                ]
            }
        }
    };

    try {
        console.log(`Sending interactive confirmation to ${phone} for order #${order}`);
        const response = await axios({
            method: 'POST',
            url: `https://graph.facebook.com/v19.0/${process.env.PHONE_NUMBER_ID}/messages`,
            data: data,
            headers: {
                'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });

        console.log(`Successfully sent confirmation buttons to ${phone}. Message ID: ${response.data.messages[0].id}`);
        res.status(200).json({ success: true, message: 'Interactive confirmation sent to client' });

    } catch (error) {
        console.error('Error sending confirmation:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
        res.status(500).json({ success: false, error: 'Failed to send WhatsApp message' });
    }
});

module.exports = router;
