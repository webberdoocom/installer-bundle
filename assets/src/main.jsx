import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import './app.css';
import './favicon.svg';

const root = ReactDOM.createRoot(document.getElementById('installer-root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
