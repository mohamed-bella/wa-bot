const express = require('express');
const router = express.Router();
const axios = require('axios');

// IPTV owner phone number (without + prefix)
const IPTV_OWNER_PHONE = '33773528321';

// Helper: send a plain text WhatsApp message via Meta Cloud API
async function sendWhatsApp(to, text) {
    const data = {
        messaging_product: 'whatsapp',
        to: to,
        type: 'text',
        text: { body: text }
    };
    const response = await axios({
        method: 'POST',
        url: `https://graph.facebook.com/v19.0/${process.env.PHONE_NUMBER_ID}/messages`,
        data: data,
        headers: {
            'Authorization': `Bearer ${process.env.WHATSAPP_TOKEN}`,
            'Content-Type': 'application/json'
        }
    });
    return response.data.messages[0].id;
}

// Format a Moroccan/international phone number
function formatPhone(phone) {
    let p = String(phone).replace(/\D/g, '');
    if (p.startsWith('0')) p = '212' + p.slice(1);
    return p;
}

// Route: POST /api/iptv-contact
// Called by the WordPress IPTV shortcode form after submission.
// Sends a WhatsApp notification to the owner AND a confirmation to the client.
router.post('/iptv-contact', async (req, res) => {
    const { name, email, phone } = req.body;

    if (!name || !phone) {
        return res.status(400).json({ success: false, error: 'name and phone are required' });
    }

    const clientPhone = formatPhone(phone);

    // ── Message to owner ──
    const ownerMsg =
        `📺 *Nouvelle Demande IPTV*\n\n` +
        `*Nom:* ${name}\n` +
        `*Email:* ${email || 'Non fourni'}\n` +
        `*WhatsApp:* +${clientPhone}\n\n` +
        `Contactez ce client dès que possible!`;

    // ── Message to client ──
    const clientMsg =
        `Bonjour *${name}*,\n\n` +
        `Merci pour votre intérêt à notre service IPTV! 📺\n\n` +
        `Nous avons bien reçu votre demande et nous vous contacterons *dès que possible* pour vous fournir tous les détails.\n\n` +
        `À bientôt! 🙏`;

    try {
        console.log(`[IPTV] New contact from ${name} (${clientPhone})`);

        // Send to owner
        await sendWhatsApp(IPTV_OWNER_PHONE, ownerMsg);
        console.log(`[IPTV] Owner notified at ${IPTV_OWNER_PHONE}`);

        // Send to client (only if their number is valid, not the same as the owner)
        if (clientPhone && clientPhone !== IPTV_OWNER_PHONE) {
            await sendWhatsApp(clientPhone, clientMsg);
            console.log(`[IPTV] Client notified at ${clientPhone}`);
        }

        res.status(200).json({ success: true, message: 'Messages sent to owner and client' });

    } catch (error) {
        console.error('[IPTV] Error sending WhatsApp messages:', error.response ? JSON.stringify(error.response.data, null, 2) : error.message);
        res.status(500).json({ success: false, error: 'Failed to send WhatsApp messages' });
    }
});

module.exports = router;
