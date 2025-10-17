import React, { useState, useEffect } from 'react';
import axios from 'axios';
import StepIndicator from './components/StepIndicator';
import SystemCheck from './components/SystemCheck';
import DatabaseConfig from './components/DatabaseConfig';
import TableInstaller from './components/TableInstaller';
import AdminSetup from './components/AdminSetup';
import SmtpSetup from './components/SmtpSetup';
import AppConfig from './components/AppConfig';
import CompletionScreen from './components/CompletionScreen';

const STEPS = [
  { id: 1, name: 'System Requirements', key: 'system_check' },
  { id: 2, name: 'Database Configuration', key: 'database_config' },
  { id: 3, name: 'Install Tables', key: 'database_tables' },
  { id: 4, name: 'Admin User', key: 'admin_user' },
  { id: 5, name: 'SMTP Configuration', key: 'smtp_config' },
  { id: 6, name: 'App Configuration', key: 'app_config' }
];

function App() {
  const [currentStep, setCurrentStep] = useState(1);
  const [installationStatus, setInstallationStatus] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    checkInstallationStatus();
  }, []);

  const checkInstallationStatus = async () => {
    try {
      const response = await axios.get('/install/api/status');
      if (response.data.success) {
        setInstallationStatus(response.data.status);
        
        // Determine current step based on status
        if (response.data.completed) {
          setCurrentStep(7); // Show completion screen
        } else if (response.data.status.smtp_config) {
          setCurrentStep(6);
        } else if (response.data.status.admin_user) {
          setCurrentStep(5);
        } else if (response.data.status.database_tables) {
          setCurrentStep(4);
        } else if (response.data.status.database_config) {
          setCurrentStep(3);
        } else {
          setCurrentStep(1);
        }
      }
    } catch (error) {
      console.error('Error checking installation status:', error);
      setCurrentStep(1);
    } finally {
      setLoading(false);
    }
  };

  const handleNext = () => {
    setCurrentStep(prev => prev + 1);
  };

  const handleBack = () => {
    setCurrentStep(prev => Math.max(1, prev - 1));
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center">
        <div className="text-center">
          <div className="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
          <p className="mt-4 text-gray-600">Loading installer...</p>
        </div>
      </div>
    );
  }

  // Completion screen gets special layout
  if (currentStep === 7) {
    return (
      <div className="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div className="w-full max-w-2xl">
          <CompletionScreen />
        </div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <div className="bg-white shadow">
        <div className="max-w-6xl mx-auto px-4 py-6">
          <h1 className="text-3xl font-bold text-gray-900">Application Installer</h1>
          <p className="mt-2 text-gray-600">Follow the steps below to install your application</p>
        </div>
      </div>

      {/* Step Indicator */}
      <div className="max-w-6xl mx-auto px-4 py-8">
        <StepIndicator 
          steps={STEPS} 
          currentStep={currentStep}
          installationStatus={installationStatus}
        />
      </div>

      {/* Main Content */}
      <div className="max-w-4xl mx-auto px-4 pb-12">
        <div className="animate-slide-in">
          {currentStep === 1 && (
            <SystemCheck onNext={handleNext} />
          )}
          
          {currentStep === 2 && (
            <DatabaseConfig onNext={handleNext} onBack={handleBack} />
          )}
          
          {currentStep === 3 && (
            <TableInstaller onNext={handleNext} onBack={handleBack} />
          )}
          
          {currentStep === 4 && (
            <AdminSetup onNext={handleNext} onBack={handleBack} />
          )}
          
          {currentStep === 5 && (
            <SmtpSetup onNext={handleNext} onBack={handleBack} />
          )}
          
          {currentStep === 6 && (
            <AppConfig onNext={handleNext} onBack={handleBack} />
          )}
        </div>
      </div>

      {/* Footer */}
      <div className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-4">
        <div className="max-w-4xl mx-auto px-4">
          <p className="text-center text-sm text-gray-500">
            Powered by Webberdoo Installer Bundle
          </p>
        </div>
      </div>
    </div>
  );
}

export default App;
