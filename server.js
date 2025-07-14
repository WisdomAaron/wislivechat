const express = require('express');
const app = express();
const PORT = process.env.PORT || 10000;

console.log('Starting server on port:', PORT);

app.get('/', (req, res) => {
  res.send('WisChat Backend is working!');
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`Server running on port ${PORT}`);
});
