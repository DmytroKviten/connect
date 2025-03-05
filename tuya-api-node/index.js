const express = require('express');
const cors = require('cors');
const axios = require('axios');
const crypto = require('crypto');

const CLIENT_ID = 'mkm9rfrq8edwr8rcja8c';
const SECRET = '1a6898ca13874149859c978fca9f5208';
const BASE_API_URL = 'https://openapi.tuyaeu.com';

const app = express();
app.use(cors());

async function fetchServerTime() {
    try {
        const response = await axios.get(`${BASE_API_URL}/v1.0/time`);
        return response.data.t;
    } catch (error) {
        console.error('Error fetching server time, retrying:', error);
        for (let i = 0; i < 3; i++) {
            try {
                const retryResponse = await axios.get(`${BASE_API_URL}/v1.0/time`);
                return retryResponse.data.t;
            } catch (retryError) {
                console.error(`Retry ${i + 1}: Failed to fetch server time`, retryError);
            }
        }
        throw new Error('Failed to synchronize time with Tuya API after several attempts');
    }
}

async function getAccessToken() {
    const t = await fetchServerTime();
    const nonce = crypto.randomBytes(16).toString('hex');
    const method = 'GET';
    const contentSHA256 = crypto.createHash('sha256').update('').digest('hex');
    const url = '/v1.0/token?grant_type=1';
    const stringToSign = `${method}\n${contentSHA256}\n\n${url}`;
    const stringToHash = `${CLIENT_ID}${t}${nonce}${stringToSign}`;
    const sign = crypto.createHmac('sha256', SECRET).update(stringToHash).digest('hex').toUpperCase();

    const headers = {
        'client_id': CLIENT_ID,
        'sign': sign,
        't': t.toString(),
        'sign_method': "HMAC-SHA256",
        'nonce': nonce,
        'Content-Type': 'application/json'
    };

    try {
        const response = await axios.get(`${BASE_API_URL}${url}`, { headers });
        if (response.status === 200 && response.data.result) {
            console.log('Token retrieved successfully:', response.data.result.access_token);
            return response.data.result.access_token;
        } else {
            console.error('Failed to retrieve the token:', response.data);
            return null;
        }
    } catch (error) {
        console.error('Error retrieving token:', error);
        return null;
    }
}

async function fetchSensorData(accessToken) {
    try {
        const sensorUrl = `${BASE_API_URL}/v1.0/devices/bfea7838726f885481awsk/status`;
        const headers = {
            'Authorization': `Bearer ${accessToken}`,
            'Content-Type': 'application/json'
        };
        const response = await axios.get(sensorUrl, { headers });
        console.log('API Response:', response.data);
        if (response.data.success && response.data.result) {
            return response.data.result;
        } else {
            throw new Error(`Failed to fetch sensor data: ${response.data.error_code} - ${response.data.msg}`);
        }
    } catch (error) {
        console.error('Error fetching sensor data:', error);
        throw error;
    }
}

app.get('/sensor_data', async (req, res) => {
    try {
        const accessToken = await getAccessToken();
        const sensorData = await fetchSensorData(accessToken);
        res.json(sensorData);
    } catch (error) {
        console.error('Error fetching sensor data:', error);
        res.status(500).send('Server Error');
    }
});

app.listen(5001, () => {
    console.log('Server is running on http://localhost:5001');
});
