import React, { useState } from 'react';
import axios from 'axios';

function SmtpSetup({ onNext, onBack }) {
  const [formData, setFormData] = useState({
    smtpHost: '',
    smtpPort: '587',
    smtpUsername: '',
    smtpPassword: '',
    smtpEncryption: 'tls',
    smtpFromEmail: '',
    smtpFromName: ''
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);
  const [skipStep, setSkipStep] = useState(false);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({
      ...formData,
      [name]: value
    });
    setError(null);
  };

  const validateForm = () => {
    // If skipping, no validation needed
    if (skipStep) {
      return true;
    }

    // Check if any field is filled
    const hasAnyValue = Object.values(formData).some(value => value.trim() !== '');
    
    if (hasAnyValue) {
      // If any field has a value, validate required fields
      if (!formData.smtpHost || !formData.smtpPort || !formData.smtpUsername) {
        setError('SMTP Host, Port, and Username are required when configuring SMTP');
        return false;
      }

      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (formData.smtpFromEmail && !emailRegex.test(formData.smtpFromEmail)) {
        setError('Please enter a valid From Email address');
        return false;
      }
    }

    return true;
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    setError(null);

    try {
      const response = await axios.post('/install/api/smtp-config', {
        ...formData,
        skip: skipStep
      });
      
      if (response.data.success) {
        setSuccess(true);
        setTimeout(() => {
          onNext();
        }, 1000);
      } else {
        setError(response.data.message || 'Failed to save SMTP configuration');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error saving SMTP configuration');
    } finally {
      setLoading(false);
    }
  };

  const handleSkip = async () => {
    setSkipStep(true);
    setLoading(true);
    setError(null);

    try {
      const response = await axios.post('/install/api/smtp-config', {
        skip: true
      });
      
      if (response.data.success) {
        setSuccess(true);
        setTimeout(() => {
          onNext();
        }, 500);
      } else {
        setError(response.data.message || 'Failed to skip SMTP configuration');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error skipping SMTP configuration');
    } finally {
      setLoading(false);
      setSkipStep(false);
    }
  };

  return (
    <div className="card">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">SMTP Configuration (Optional)</h2>
      <p className="text-gray-600 mb-6">
        Configure SMTP settings for sending emails. This step is optional and can be configured later.
      </p>

      <form onSubmit={handleSubmit} className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              SMTP Host
            </label>
            <input
              type="text"
              name="smtpHost"
              value={formData.smtpHost}
              onChange={handleChange}
              className="input"
              placeholder="smtp.example.com"
              disabled={loading}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              SMTP Port
            </label>
            <input
              type="number"
              name="smtpPort"
              value={formData.smtpPort}
              onChange={handleChange}
              className="input"
              placeholder="587"
              disabled={loading}
            />
          </div>
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            SMTP Username
          </label>
          <input
            type="text"
            name="smtpUsername"
            value={formData.smtpUsername}
            onChange={handleChange}
            className="input"
            placeholder="username@example.com"
            disabled={loading}
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            SMTP Password
          </label>
          <input
            type="password"
            name="smtpPassword"
            value={formData.smtpPassword}
            onChange={handleChange}
            className="input"
            placeholder="Enter SMTP password"
            disabled={loading}
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-gray-700 mb-2">
            Encryption
          </label>
          <select
            name="smtpEncryption"
            value={formData.smtpEncryption}
            onChange={handleChange}
            className="input"
            disabled={loading}
          >
            <option value="tls">TLS (Recommended)</option>
            <option value="ssl">SSL</option>
            <option value="none">None</option>
          </select>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              From Email (Optional)
            </label>
            <input
              type="email"
              name="smtpFromEmail"
              value={formData.smtpFromEmail}
              onChange={handleChange}
              className="input"
              placeholder="noreply@example.com"
              disabled={loading}
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-2">
              From Name (Optional)
            </label>
            <input
              type="text"
              name="smtpFromName"
              value={formData.smtpFromName}
              onChange={handleChange}
              className="input"
              placeholder="My Application"
              disabled={loading}
            />
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
              <p className="text-sm text-green-700">SMTP configuration saved successfully!</p>
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
          <div className="flex gap-3">
            <button
              type="button"
              onClick={handleSkip}
              className="btn btn-secondary"
              disabled={loading}
            >
              Skip for Now
            </button>
            <button
              type="submit"
              className="btn btn-primary"
              disabled={loading}
            >
              {loading ? 'Saving...' : 'Save & Continue'}
            </button>
          </div>
        </div>
      </form>
    </div>
  );
}

export default SmtpSetup;
