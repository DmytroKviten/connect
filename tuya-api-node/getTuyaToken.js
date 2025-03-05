require('dotenv').config();
const axios = require('axios');
const crypto = require('crypto');
const { v4: uuidv4 } = require('uuid');

const CLIENT_ID = 'mkm9rfrq8edwr8rcja8c';
const SECRET_KEY = '1a6898ca13874149859c978fca9f5208';
const ENDPOINT = 'https://openapi.tuyaeu.com';

function generateSignature(clientId, secret, timestamp, nonce, method, url) {
    const stringToSign = `${method}\n${url}`;
    const stringToHash = `${clientId}${timestamp}${nonce}${stringToSign}`;
    console.log("String to Hash:", stringToHash);
    const hmac = crypto.createHmac('sha256', secret);
    const signature = hmac.update(stringToHash).digest('hex');
    return signature.toUpperCase();
}

async function getToken() {
    const timestamp = new Date().getTime().toString();
    const nonce = uuidv4();
    const method = "GET";
    const url = "/v1.0/token?grant_type=1";
    const signature = generateSignature(CLIENT_ID, SECRET_KEY, timestamp, nonce, method, url);

    try {
        const response = await axios.get(`${ENDPOINT}${url}`, {
            headers: {
                client_id: CLIENT_ID,
                sign: signature,
                t: timestamp,
                nonce: nonce,
                sign_method: 'HMAC-SHA256'
            }
        });
        console.log('API Response:', response.data);
        if (response.data.success) {
            console.log('Access Token:', response.data.result.access_token);
            console.log('Refresh Token:', response.data.result.refresh_token);
            console.log('Token Expires In:', response.data.result.expire_time);
        } else {
            console.log('Failed to fetch token:', response.data.msg);
        }
    } catch (error) {
        console.error('Error fetching Tuya token:', error.response ? error.response.data : error.message);
    }
}

getToken();





