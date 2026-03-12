const express = require('express');
const router = express.Router();
const axios = require('axios');

// Route: POST /api/confirmation
router.post('/confirmation', async (req, res) => {
    const { phone, customerName, orderNumber } = req.body;

    // Basic validation
    if (!phone) {
        return res.status(400).json({ success: false, error: 'Phone number is required' });
    }

    // Compose the message you want to send to the client
    const message = `Hello ${customerName || 'Customer'}!\n\nThank you for your order (Order #${orderNumber || 'Pending'}).\nWe have received your request and are processing it now! ✅`;

    // Format WhatsApp message payload
    const data = {
        messaging_product: 'whatsapp',
        to: phone,
        type: 'text',
        text: { body: message }
    };

    try {
        console.log(`Sending API order confirmation to ${phone}`);
        // Send actual message using axios
        const response = await axios({
            method: 'POST',
            url: `https://graph.facebook.com/v19.0/${process.env.PHONE_NUMBER_ID}/messages`,
            data: data,
            headers: {
                'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}`,
                'Content-Type': 'application/json'
            }
        });
        
        console.log(`Successfully sent order confirmation to ${phone}. Message ID: ${response.data.messages[0].id}`);
        
        // Respond back to your website that the request was handled successfully
        res.status(200).json({ success: true, message: 'WhatsApp message sent to client' });
        
    } catch (error) {
        console.error('Error sending order confirmation message:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
        res.status(500).json({ success: false, error: 'Failed to send WhatsApp message' });
    }
});

module.exports = router;
