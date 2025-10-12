import React, { useState } from 'react';
import axios from 'axios';

function DatabaseConfig({ onNext, onBack }) {
  const [formData, setFormData] = useState({
    host: 'localhost',
    port: '3306',
    db_name: '',
    db_user: '',
    password: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
    setError(null);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post('/install/api/database-config', formData);
      
      if (response.data.success) {
        setSuccess(true);
        setTimeout(() => {
          onNext();
        }, 1000);
      } else {
        setError(response.data.message || 'Failed to save database configuration');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error connecting to database');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Database Configuration</h2>
      <p className="text-gray-600 mb-6">
        Enter your database connection details. Make sure the database already exists.
      </p>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Database Host
          </label>
          <input
            type="text"
            name="host"
            value={formData.host}
            onChange={handleChange}
            className="input"
            required
            placeholder="localhost"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Database Port
          </label>
          <input
            type="number"
            name="port"
            value={formData.port}
            onChange={handleChange}
            className="input"
            required
            placeholder="3306"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Database Name
          </label>
          <input
            type="text"
            name="db_name"
            value={formData.db_name}
            onChange={handleChange}
            className="input"
            required
            placeholder="my_database"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Database User
          </label>
          <input
            type="text"
            name="db_user"
            value={formData.db_user}
            onChange={handleChange}
            className="input"
            required
            placeholder="root"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Database Password
          </label>
          <input
            type="password"
            name="password"
            value={formData.password}
            onChange={handleChange}
            className="input"
            placeholder="Enter password"
          />
        </div>

        {error && (
          <div className="p-4 bg-red-50 border border-red-200 rounded-lg">
            <div className="flex items-start">
              <svg className="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
              </svg>
              <p className="text-sm text-red-700">{error}</p>
            </div>
          </div>
        )}

        {success && (
          <div className="p-4 bg-green-50 border border-green-200 rounded-lg">
            <div className="flex items-start">
              <svg className="w-5 h-5 text-green-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
              </svg>
              <p className="text-sm text-green-700">Database configuration saved successfully!</p>
            </div>
          </div>
        )}

        <div className="flex justify-between pt-4">
          <button
            type="button"
            onClick={onBack}
            className="btn btn-secondary"
            disabled={loading}
          >
            Back
          </button>
          <button
            type="submit"
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? 'Testing Connection...' : 'Save & Continue'}
          </button>
        </div>
      </form>
    </div>
  );
}

export default DatabaseConfig;
