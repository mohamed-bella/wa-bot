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

    // Sanitize values for use in button IDs:
    // WhatsApp only allows alphanumeric, hyphens, and underscores in button IDs.
    const safeOrder = String(order).replace(/[^a-zA-Z0-9]/g, '-');
    const safeName  = String(name).replace(/[^a-zA-Z0-9]/g, '-');

    // Use a pre-approved WhatsApp Template message.
    // This is REQUIRED for business-initiated conversations (e.g. sending on order placement).
    // Regular interactive messages only work inside an active 24-hour customer-service window.
    //
    // ⚙️  Template name must match EXACTLY what you created in Meta WhatsApp Manager.
    //     Change 'order_confirmation' below if you used a different name.
    const TEMPLATE_NAME = 'order_confirmation';
    const TEMPLATE_LANG = 'fr'; // Set to 'en' if you created the template in English

    const data = {
        messaging_product: 'whatsapp',
        to: phone,
        type: 'template',
        template: {
            name: TEMPLATE_NAME,
            language: { code: TEMPLATE_LANG },
            components: [
                {
                    // Fills in the {{1}} and {{2}} variables in your template body
                    type: 'body',
                    parameters: [
                        { type: 'text', text: String(order) },  // {{1}} = orderNumber
                        { type: 'text', text: String(name) }    // {{2}} = customerName
                    ]
                },
                {
                    // Encodes the button IDs for the quick-reply buttons in the template.
                    // When the customer taps a button, WhatsApp fires a button_reply webhook
                    // with the payload below — index.js already handles this correctly.
                    type: 'button',
                    sub_type: 'quick_reply',
                    index: '0',
                    parameters: [{ type: 'payload', payload: `confirm-${safeOrder}-${safeName}` }]
                },
                {
                    type: 'button',
                    sub_type: 'quick_reply',
                    index: '1',
                    parameters: [{ type: 'payload', payload: `cancel-${safeOrder}-${safeName}` }]
                }
            ]
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

// Route: POST /api/send-message
// Legacy-compatible endpoint used by WordPress bot.php (basma_bot_send_message function).
// Accepts { phone, message } and sends a plain text WhatsApp message.
router.post('/send-message', async (req, res) => {
    const { phone, message } = req.body;

    if (!phone || !message) {
        return res.status(400).json({ success: false, error: 'phone and message are required' });
    }

    const data = {
        messaging_product: 'whatsapp',
        to: phone,
        type: 'text',
        text: { body: message }
    };

    try {
        console.log(`[send-message] Sending to ${phone}`);
        const response = await axios({
            method: 'POST',
            url: `https://graph.facebook.com/v19.0/${process.env.PHONE_NUMBER_ID}/messages`,
            data: data,
            headers: {
                'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });

        console.log(`[send-message] Sent to ${phone}. Message ID: ${response.data.messages[0].id}`);
        res.status(200).json({ success: true, message: 'Message sent' });

    } catch (error) {
        console.error('[send-message] Error:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
        res.status(500).json({ success: false, error: 'Failed to send WhatsApp message' });
    }
});

module.exports = router;

