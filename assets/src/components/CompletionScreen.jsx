import React from 'react';

function CompletionScreen() {
  const handleGoToLogin = () => {
    // Get the current pathname and replace /install with /login
    const pathname = window.location.pathname;
    const newPath = pathname.replace(/\/install.*$/, '/login');
    
    // Build the full URL manually
    const protocol = window.location.protocol; // http: or https:
    const host = window.location.host; // 127.0.0.1:8000
    const loginUrl = `${protocol}//${host}${newPath}`;
    
    console.log('Redirecting to:', loginUrl); // Debug log
    window.location.href = loginUrl;
  };

  return (
    <div className="card text-center">
      <div className="mb-6">
        <div className="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full">
          <svg className="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
      </div>

      <h2 className="text-3xl font-bold text-gray-900 mb-4">Installation Complete!</h2>
      <p className="text-lg text-gray-600 mb-8">
        Your application has been successfully installed and configured.
      </p>

      <div className="bg-green-50 border border-green-200 rounded-lg p-6 mb-8 text-left">
        <h3 className="font-semibold text-green-900 mb-3">What's Next?</h3>
        <ul className="space-y-2 text-sm text-green-800">
          <li className="flex items-start">
            <svg className="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Log in with your admin credentials to access the dashboard</span>
          </li>
          <li className="flex items-start">
            <svg className="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>All database tables have been created successfully</span>
          </li>
          <li className="flex items-start">
            <svg className="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Application configuration has been saved to config files</span>
          </li>
          <li className="flex items-start">
            <svg className="w-5 h-5 text-green-600 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Your admin account is ready to use</span>
          </li>
        </ul>
      </div>

      <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8 text-left">
        <div className="flex items-start">
          <svg className="w-5 h-5 text-yellow-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
          </svg>
          <div>
            <h3 className="font-medium text-yellow-900 text-sm">Important Security Note</h3>
            <p className="mt-1 text-sm text-yellow-800">
              For security reasons, consider removing or protecting the installer route after installation.
              The installer has created a marker file to prevent reinstallation.
            </p>
          </div>
        </div>
      </div>

      <div className="space-y-4">
        <button
          onClick={handleGoToLogin}
          className="btn btn-primary w-full sm:w-auto px-8"
        >
          Go to Login
        </button>
        
        <div className="pt-4 border-t border-gray-200">
          <p className="text-sm text-gray-500">
            Need help? Check the documentation or contact support.
          </p>
        </div>
      </div>
    </div>
  );
}

export default CompletionScreen;
