require('dotenv').config();
const axios = require('axios');
const CryptoJS = require('crypto-js');

const { TUYA_CLIENT_ID, TUYA_SECRET_KEY, TUYA_ENDPOINT } = process.env;

function generateSignature(parameters, secretKey) {
    const sortedKeys = Object.keys(parameters).sort();
    const baseString = sortedKeys.map(key => `${key}${parameters[key]}`).join('');
    const sign = CryptoJS.HmacSHA256(baseString, secretKey).toString().toUpperCase();
    return sign;
}

async function getToken() {
    const timestamp = new Date().getTime().toString();
    const params = {
        client_id: TUYA_CLIENT_ID,
        grant_type: '1', // Simple mode
        timestamp: timestamp,
        sign_method: 'HMAC-SHA256'
    };

    const sign = generateSignature(params, TUYA_SECRET_KEY);

    try {
        const response = await axios.get(`${TUYA_ENDPOINT}/v1.0/token?grant_type=1`, {
            headers: {
                'client_id': TUYA_CLIENT_ID,
                'sign': sign,
                't': timestamp,
                'sign_method': 'HMAC-SHA256'
            }
        });

        console.log('Access Token:', response.data.result.access_token);
        console.log('Refresh Token:', response.data.result.refresh_token);
        console.log('Token Expires In:', response.data.result.expire_time);
    } catch (error) {
        console.error('Error fetching Tuya token:', error.response ? error.response.data : error.message);
    }
}

getToken();