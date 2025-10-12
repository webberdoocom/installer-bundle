import React, { useState, useEffect } from 'react';
import axios from 'axios';

function SystemCheck({ onNext }) {
  const [checks, setChecks] = useState(null);
  const [loading, setLoading] = useState(true);
  const [canProceed, setCanProceed] = useState(false);

  useEffect(() => {
    performSystemCheck();
  }, []);

  const performSystemCheck = async () => {
    try {
      const response = await axios.get('/install/api/system-check');
      if (response.data.success) {
        setChecks(response.data.checks);
        setCanProceed(response.data.can_proceed);
      }
    } catch (error) {
      console.error('System check failed:', error);
    } finally {
      setLoading(false);
    }
  };

  const getStatusIcon = (status, critical) => {
    if (status) {
      return (
        <svg className="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      );
    } else if (critical) {
      return (
        <svg className="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      );
    } else {
      return (
        <svg className="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      );
    }
  };

  if (loading) {
    return (
      <div className="card">
        <div className="text-center py-12">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Checking system requirements...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="card">
      <h2 className="text-2xl font-bold text-gray-900 mb-6">System Requirements Check</h2>
      
      <div className="space-y-4">
        {checks && Object.entries(checks).map(([key, check]) => (
          <div 
            key={key}
            className={`flex items-center justify-between p-4 rounded-lg border ${
              check.status ? 'bg-green-50 border-green-200' :
              check.critical ? 'bg-red-50 border-red-200' :
              'bg-yellow-50 border-yellow-200'
            }`}
          >
            <div className="flex items-center space-x-3 flex-1">
              {getStatusIcon(check.status, check.critical)}
              <div>
                <p className="font-medium text-gray-900">{check.name}</p>
                <p className="text-sm text-gray-600">Required: {check.required}</p>
              </div>
            </div>
            <div className="text-right">
              <p className={`font-medium ${
                check.status ? 'text-green-700' :
                check.critical ? 'text-red-700' :
                'text-yellow-700'
              }`}>
                {check.current}
              </p>
            </div>
          </div>
        ))}
      </div>

      {!canProceed && (
        <div className="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
          <div className="flex items-start">
            <svg className="w-5 h-5 text-red-600 mt-0.5 mr-3" fill="currentColor" viewBox="0 0 20 20">
              <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clipRule="evenodd" />
            </svg>
            <div>
              <h3 className="font-medium text-red-800">Critical Requirements Not Met</h3>
              <p className="mt-1 text-sm text-red-700">
                Please fix the critical issues above before proceeding with the installation.
              </p>
            </div>
          </div>
        </div>
      )}

      <div className="mt-8 flex justify-end">
        <button
          onClick={onNext}
          disabled={!canProceed}
          className="btn btn-primary"
        >
          Continue to Database Configuration
        </button>
      </div>
    </div>
  );
}

export default SystemCheck;
