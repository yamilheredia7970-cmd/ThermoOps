import React, { useState } from 'react';
import { Wind, Lock, Mail, ArrowRight } from 'lucide-react';
import { Button } from '../components/ui';

interface LoginProps {
  onLogin: () => void;
}

export function Login({ onLogin }: LoginProps) {
  const [email, setEmail] = useState('admin@thermoops.com');
  const [password, setPassword] = useState('demo1234');
  const [isLoading, setIsLoading] = useState(false);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    // Simulate network delay
    setTimeout(() => {
      setIsLoading(false);
      onLogin();
    }, 800);
  };

  return (
    <div className="min-h-screen flex bg-surface-50">
      {/* Left side - Branding & Value Prop */}
      <div className="hidden lg:flex flex-col justify-between w-1/2 bg-surface-900 text-white p-12 relative overflow-hidden">
        {/* Background abstract pattern */}
        <div className="absolute inset-0 opacity-10">
          <div className="absolute -top-24 -left-24 w-96 h-96 bg-primary-500 rounded-full blur-3xl"></div>
          <div className="absolute bottom-0 right-0 w-[500px] h-[500px] bg-accent-500 rounded-full blur-3xl"></div>
        </div>

        <div className="relative z-10 flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-primary-500 flex items-center justify-center text-white font-bold text-xl shadow-lg">
            T
          </div>
          <div>
            <h1 className="text-white font-bold tracking-wide text-2xl leading-none">ThermoOps</h1>
            <p className="text-[11px] text-primary-300 font-bold tracking-widest uppercase mt-1">HVAC Operations</p>
          </div>
        </div>

        <div className="relative z-10 max-w-md">
          <h2 className="text-4xl font-bold leading-tight mb-6">
            Streamline your field operations and dispatching.
          </h2>
          <p className="text-surface-300 text-lg mb-8">
            The all-in-one platform built specifically for HVAC businesses to manage technicians, work orders, equipment, and inventory with precision.
          </p>
          
          <div className="space-y-4">
            <div className="flex items-center gap-3 text-surface-200">
              <div className="w-6 h-6 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400">✓</div>
              <span>Real-time dispatch calendar & tracking</span>
            </div>
            <div className="flex items-center gap-3 text-surface-200">
              <div className="w-6 h-6 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400">✓</div>
              <span>Automated inventory thresholds</span>
            </div>
            <div className="flex items-center gap-3 text-surface-200">
              <div className="w-6 h-6 rounded-full bg-primary-500/20 flex items-center justify-center text-primary-400">✓</div>
              <span>Detailed equipment service history</span>
            </div>
          </div>
        </div>

        <div className="relative z-10 text-surface-400 text-sm">
          © {new Date().getFullYear()} ThermoOps Inc. All rights reserved.
        </div>
      </div>

      {/* Right side - Login Form */}
      <div className="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12">
        <div className="w-full max-w-md">
          <div className="lg:hidden flex items-center gap-3 mb-10">
            <div className="w-10 h-10 rounded-xl bg-primary-600 flex items-center justify-center text-white font-bold text-xl shadow-lg">
              T
            </div>
            <div>
              <h1 className="text-surface-900 font-bold tracking-wide text-2xl leading-none">ThermoOps</h1>
              <p className="text-[11px] text-primary-600 font-bold tracking-widest uppercase mt-1">HVAC Operations</p>
            </div>
          </div>

          <div className="mb-8">
            <h2 className="text-2xl font-bold text-surface-900">Welcome back</h2>
            <p className="text-surface-500 mt-2">Please enter your credentials to access your dashboard.</p>
          </div>

          <form onSubmit={handleSubmit} className="space-y-5">
            <div>
              <label className="block text-sm font-semibold text-surface-700 mb-1.5">Work Email</label>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Mail className="h-5 w-5 text-surface-400" />
                </div>
                <input
                  type="email"
                  required
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="block w-full pl-10 pr-3 py-2.5 border border-surface-200 rounded-lg text-surface-900 bg-surface-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                  placeholder="admin@thermoops.com"
                />
              </div>
            </div>

            <div>
              <div className="flex items-center justify-between mb-1.5">
                <label className="block text-sm font-semibold text-surface-700">Password</label>
                <a href="#" className="text-sm font-medium text-primary-600 hover:text-primary-500">
                  Forgot password?
                </a>
              </div>
              <div className="relative">
                <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Lock className="h-5 w-5 text-surface-400" />
                </div>
                <input
                  type="password"
                  required
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="block w-full pl-10 pr-3 py-2.5 border border-surface-200 rounded-lg text-surface-900 bg-surface-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                  placeholder="••••••••"
                />
              </div>
            </div>

            <div className="pt-2">
              <Button type="submit" className="w-full justify-center" size="lg" disabled={isLoading}>
                {isLoading ? 'Authenticating...' : (
                  <>
                    Sign In <ArrowRight className="w-4 h-4 ml-1" />
                  </>
                )}
              </Button>
            </div>
            
            <div className="text-center mt-6">
              <p className="text-sm text-surface-500">
                Don't have an account?{' '}
                <a href="#" className="font-medium text-primary-600 hover:text-primary-500">
                  Contact Sales
                </a>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
}
