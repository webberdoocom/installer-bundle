import React, { useState } from 'react';
import axios from 'axios';

function TableInstaller({ onNext, onBack }) {
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);
  const [result, setResult] = useState(null);

  const handleInstall = async () => {
    setLoading(true);
    setError(null);
    setSuccess(false);

    try {
      const response = await axios.post('/install/api/install-tables');
      
      if (response.data.success) {
        setSuccess(true);
        setResult(response.data);
      } else {
        setError(response.data.message || 'Failed to install database tables');
      }
    } catch (err) {
      setError(err.response?.data?.message || 'Error installing database tables');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="card">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">Install Database Tables</h2>
      <p className="text-gray-600 mb-6">
        This step will create all necessary database tables based on your configured entities.
      </p>

      {!loading && !success && !error && (
        <div className="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
          <div className="flex items-start">
            <svg className="w-6 h-6 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
              <h3 className="font-medium text-blue-900">What will happen?</h3>
              <ul className="mt-2 text-sm text-blue-800 list-disc list-inside space-y-1">
                <li>Database connection will be verified</li>
                <li>All configured entity tables will be created</li>
                <li>Indexes and foreign keys will be set up</li>
                <li>Existing tables will not be modified (safe mode)</li>
              </ul>
            </div>
          </div>
        </div>
      )}

      {loading && (
        <div className="text-center py-12">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Creating database tables...</p>
          <p className="mt-2 text-sm text-gray-500">This may take a few moments</p>
        </div>
      )}

      {error && (
        <div className="p-4 bg-red-50 border border-red-200 rounded-lg mb-6">
          <div className="flex items-start">
            <svg className="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
            <div>
              <h3 className="font-medium text-red-800">Installation Failed</h3>
              <p className="mt-1 text-sm text-red-700">{error}</p>
            </div>
          </div>
        </div>
      )}

      {success && result && (
        <div className="p-6 bg-green-50 border border-green-200 rounded-lg mb-6">
          <div className="flex items-start">
            <svg className="w-6 h-6 text-green-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
            </svg>
            <div>
              <h3 className="font-medium text-green-800">Tables Installed Successfully!</h3>
              <p className="mt-1 text-sm text-green-700">{result.message}</p>
              {result.entities_installed && (
                <p className="mt-1 text-sm text-green-700">
                  {result.entities_installed} entities installed
                </p>
              )}
            </div>
          </div>
        </div>
      )}

      <div className="flex justify-between pt-4">
        <button
          onClick={onBack}
          className="btn btn-secondary"
          disabled={loading}
        >
          Back
        </button>
        
        {!success ? (
          <button
            onClick={handleInstall}
            className="btn btn-primary"
            disabled={loading}
          >
            {loading ? 'Installing...' : 'Install Tables'}
          </button>
        ) : (
          <button
            onClick={onNext}
            className="btn btn-primary"
          >
            Continue to Admin Setup
          </button>
        )}
      </div>
    </div>
  );
}

export default TableInstaller;
