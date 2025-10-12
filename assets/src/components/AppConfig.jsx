import React, { useState } from 'react';
import axios from 'axios';

function AppConfig({ onNext, onBack }) {
  const [formData, setFormData] = useState({
    base_url: window.location.origin,
    base_path: '/'
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
      const response = await axios.post('/install/api/app-config', formData);
      
      if (response.data.success) {
        setSuccess(true);
        setTimeout(() => {
          onNext();
        }, 1500);
      } else {
        setError(response.data.message || 'Failed to save application configuration');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error saving application configuration');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Application Configuration</h2>
      <p className="text-gray-600 mb-6">
        Configure your application settings. These can be changed later in the configuration files.
      </p>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Base URL
          </label>
          <input
            type="url"
            name="base_url"
            value={formData.base_url}
            onChange={handleChange}
            className="input"
            required
            placeholder="https://example.com"
          />
          <p className="mt-1 text-xs text-gray-500">
            The base URL of your application (auto-detected from current location)
          </p>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Base Path
          </label>
          <input
            type="text"
            name="base_path"
            value={formData.base_path}
            onChange={handleChange}
            className="input"
            required
            placeholder="/"
          />
          <p className="mt-1 text-xs text-gray-500">
            The base path if your application is in a subdirectory (e.g., /myapp)
          </p>
        </div>

        <div className="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div className="flex items-start">
            <svg className="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h3 className="font-medium text-blue-900 text-sm">Final Step</h3>
              <p className="mt-1 text-sm text-blue-800">
                After completing this step, your application will be fully installed and ready to use.
                You'll be able to log in with the admin credentials you created.
              </p>
            </div>
          </div>
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
              <p className="text-sm text-green-700">Configuration saved successfully!</p>
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
            {loading ? 'Completing Installation...' : 'Complete Installation'}
          </button>
        </div>
      </form>
    </div>
  );
}

export default AppConfig;
