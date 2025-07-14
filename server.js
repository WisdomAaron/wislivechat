const express = require('express');
const app = express();
const PORT = process.env.PORT || 3000;

console.log('Starting server on port:', PORT);

// Basic middleware
app.use(express.json());

// Simple test endpoint
app.get('/', (req, res) => {
  res.json({ message: 'WisChat Backend is working!' });
});

app.get('/api/v1/health', (req, res) => {
  res.json({ status: 'OK', message: 'Health check passed' });
});

app.get('/api/v1/firebase/config', (req, res) => {
  res.json({
    apiKey: "AIzaSyAj-OZt3MnfRPJYt0dqeMVgIc4peJGQWJQ",
    authDomain: "wis-livechat.firebaseapp.com",
    projectId: "wis-livechat"
  });
});

app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
