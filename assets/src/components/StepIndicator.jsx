import React from 'react';

function StepIndicator({ steps, currentStep, installationStatus }) {
  const getStepStatus = (stepId, stepKey) => {
    if (stepId < currentStep) return 'completed';
    if (stepId === currentStep) return 'active';
    if (installationStatus && installationStatus[stepKey]) return 'completed';
    return 'pending';
  };

  return (
    <div className="w-full">
      <div className="flex items-center justify-between">
        {steps.map((step, index) => {
          const status = getStepStatus(step.id, step.key);
          
          return (
            <React.Fragment key={step.id}>
              <div className="flex flex-col items-center flex-1">
                {/* Step Circle */}
                <div className={`step-indicator ${
                  status === 'completed' ? 'step-completed' :
                  status === 'active' ? 'step-active' :
                  'step-pending'
                }`}>
                  {status === 'completed' ? (
                    <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
                    </svg>
                  ) : (
                    <span>{step.id}</span>
                  )}
                </div>
                
                {/* Step Label */}
                <p className={`mt-2 text-sm font-medium text-center ${
                  status === 'active' ? 'text-blue-600' :
                  status === 'completed' ? 'text-green-600' :
                  'text-gray-500'
                }`}>
                  {step.name}
                </p>
              </div>
              
              {/* Connector Line */}
              {index < steps.length - 1 && (
                <div className="flex-1 h-0.5 mx-4 mb-8">
                  <div className={`h-full ${
                    getStepStatus(step.id + 1, steps[index + 1].key) === 'completed' ||
                    getStepStatus(step.id + 1, steps[index + 1].key) === 'active'
                      ? 'bg-blue-600'
                      : 'bg-gray-300'
                  }`}></div>
                </div>
              )}
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
}

export default StepIndicator;
