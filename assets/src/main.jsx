import React from 'react';
import ReactDOM from 'react-dom/client';
import axios from 'axios';
import App from './App';
import './app.css';
import './favicon.svg';

// Configure axios to use relative URLs
// This will automatically work with subfolders
const currentPath = window.location.pathname;
const basePath = currentPath.replace(/\/install.*$/, '');
axios.defaults.baseURL = basePath || '/';

console.log('Axios baseURL set to:', axios.defaults.baseURL);

const root = ReactDOM.createRoot(document.getElementById('installer-root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);
