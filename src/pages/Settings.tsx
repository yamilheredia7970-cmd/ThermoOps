import React, { useState } from 'react';
import { 
  Building2, Users, Shield, Bell, Database, 
  Settings as SettingsIcon, Wrench, FileCheck, MapPin, 
  Package, Smartphone, Activity, Moon, Sun, Lock, User
} from 'lucide-react';
import { Card, Badge, Button } from '../components/ui';
import { useTheme } from '../contexts/ThemeContext';

const SETTINGS_GROUPS = [
  {
    title: 'Account',
    items: [
      { id: 'profile', name: 'My Profile', icon: User },
      { id: 'security', name: 'Account Security', icon: Lock },
      { id: 'notifications', name: 'Notifications', icon: Bell },
    ]
  },
  {
    title: 'Company',
    items: [
      { id: 'company_info', name: 'Company Information', icon: Building2 },
      { id: 'users', name: 'Users & Roles', icon: Shield },
      { id: 'technicians', name: 'Technicians', icon: Wrench },
      { id: 'locations', name: 'Locations', icon: MapPin },
    ]
  },
  {
    title: 'Operations',
    items: [
      { id: 'wo_settings', name: 'Work Order Settings', icon: FileCheck },
      { id: 'service_types', name: 'Service Types', icon: SettingsIcon },
      { id: 'equipment_types', name: 'Equipment Types', icon: Database },
      { id: 'inventory', name: 'Inventory', icon: Package },
    ]
  },
  {
    title: 'Mobile',
    items: [
      { id: 'mobile_access', name: 'Technician Mobile Access', icon: Smartphone },
      { id: 'mobile_permissions', name: 'Mobile Permissions', icon: Shield },
      { id: 'mobile_settings', name: 'Technician App Settings', icon: SettingsIcon },
    ]
  },
  {
    title: 'System',
    items: [
      { id: 'integrations', name: 'Integrations', icon: Database },
      { id: 'audit_log', name: 'Audit Log', icon: Activity },
      { id: 'system_prefs', name: 'System Preferences', icon: SettingsIcon },
    ]
  }
];

export function Settings() {
  const [activeTab, setActiveTab] = useState('profile');
  const { theme, toggleTheme } = useTheme();

  return (
    <div className="max-w-6xl mx-auto space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-surface-900">Settings</h1>
        <p className="text-sm text-surface-500 mt-1">Manage application configuration and user preferences.</p>
      </div>

      <div className="flex flex-col md:flex-row gap-8 items-start">
        <aside className="w-full md:w-64 flex-shrink-0 space-y-6">
          {SETTINGS_GROUPS.map((group, gIdx) => (
            <div key={gIdx}>
              <h3 className="text-xs font-bold text-surface-400 uppercase tracking-wider mb-2 px-3">{group.title}</h3>
              <nav className="flex flex-col gap-1">
                {group.items.map(item => {
                  const isActive = activeTab === item.id;
                  return (
                    <button
                      key={item.id}
                      onClick={() => setActiveTab(item.id)}
                      className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors text-left
                        ${isActive 
                          ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400' 
                          : 'text-surface-600 hover:bg-surface-100 hover:text-surface-900'}`}
                    >
                      <item.icon className={`w-4 h-4 ${isActive ? 'text-primary-600 dark:text-primary-400' : 'text-surface-400'}`} />
                      {item.name}
                    </button>
                  );
                })}
              </nav>
            </div>
          ))}
        </aside>

        <main className="flex-1 w-full space-y-6">
          
          {/* PROFILE */}
          {activeTab === 'profile' && (
            <Card className="p-6">
              <h2 className="text-lg font-bold text-surface-900 mb-4">My Profile</h2>
              <div className="space-y-4 max-w-2xl">
                <div className="flex items-center gap-6 mb-6">
                  <div className="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-2xl font-bold">
                    AD
                  </div>
                  <div>
                    <Button variant="outline" size="sm">Change Avatar</Button>
                  </div>
                </div>
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label className="block text-sm font-semibold text-surface-700 mb-1.5">First Name</label>
                    <input type="text" defaultValue="Admin" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-surface-700 mb-1.5">Last Name</label>
                    <input type="text" defaultValue="User" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-semibold text-surface-700 mb-1.5">Email Address</label>
                  <input type="email" defaultValue="admin@thermoops.com" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                </div>
                <div className="pt-4 border-t border-surface-100 flex justify-end">
                  <Button>Save Profile</Button>
                </div>
              </div>
            </Card>
          )}

          {/* SYSTEM PREFERENCES (Theme Toggle) */}
          {activeTab === 'system_prefs' && (
            <Card className="p-6">
              <h2 className="text-lg font-bold text-surface-900 mb-4">System Preferences</h2>
              <div className="space-y-6">
                <div>
                  <h3 className="text-sm font-bold text-surface-900 mb-2">Appearance</h3>
                  <div className="flex items-center justify-between p-4 border border-surface-200 rounded-lg bg-surface-50">
                    <div className="flex items-center gap-3">
                      {theme === 'dark' ? <Moon className="w-5 h-5 text-primary-500" /> : <Sun className="w-5 h-5 text-surface-400" />}
                      <div>
                        <p className="font-medium text-surface-900">Dark Mode</p>
                        <p className="text-xs text-surface-500">Toggle high-contrast dark theme across the application.</p>
                      </div>
                    </div>
                    <button 
                      onClick={toggleTheme}
                      className={`relative w-12 h-6 rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 ${theme === 'dark' ? 'bg-primary-500' : 'bg-surface-300'}`}
                    >
                      <span className={`absolute left-1 top-1 bg-white w-4 h-4 rounded-full transition-transform ${theme === 'dark' ? 'translate-x-6' : 'translate-x-0'}`} />
                    </button>
                  </div>
                </div>
                <div>
                  <h3 className="text-sm font-bold text-surface-900 mb-2">Timezone & Localization</h3>
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-semibold text-surface-700 mb-1.5">System Timezone</label>
                      <select className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option>America/New_York (EST)</option>
                        <option>America/Chicago (CST)</option>
                        <option>America/Denver (MST)</option>
                        <option>America/Los_Angeles (PST)</option>
                      </select>
                    </div>
                    <div>
                      <label className="block text-sm font-semibold text-surface-700 mb-1.5">Date Format</label>
                      <select className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option>MM/DD/YYYY</option>
                        <option>DD/MM/YYYY</option>
                        <option>YYYY-MM-DD</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            </Card>
          )}

          {/* COMPANY INFO */}
          {activeTab === 'company_info' && (
             <Card className="p-6">
                <h2 className="text-lg font-bold text-surface-900 mb-4">Company Information</h2>
                <div className="space-y-4 max-w-2xl">
                  <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-sm font-semibold text-surface-700 mb-1.5">Company Name</label>
                      <input type="text" defaultValue="ThermoOps Demo Corp" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                    <div>
                      <label className="block text-sm font-semibold text-surface-700 mb-1.5">Business Registration Number</label>
                      <input type="text" defaultValue="EIN-9283719" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                    </div>
                  </div>
                  <div>
                    <label className="block text-sm font-semibold text-surface-700 mb-1.5">Support Email</label>
                    <input type="email" defaultValue="support@thermoops.com" className="block w-full px-3 py-2 border border-surface-200 bg-surface-50 rounded-lg text-surface-900 focus:outline-none focus:ring-2 focus:ring-primary-500" />
                  </div>
                  <div className="pt-4 border-t border-surface-100 flex justify-end">
                    <Button>Save Changes</Button>
                  </div>
                </div>
              </Card>
          )}

          {/* USERS & ROLES */}
          {activeTab === 'users' && (
            <Card className="p-6">
              <div className="flex items-center justify-between mb-6">
                <div>
                  <h2 className="text-lg font-bold text-surface-900">Users & Roles</h2>
                  <p className="text-sm text-surface-500">Manage user access and permission schemas.</p>
                </div>
                <Button size="sm">Invite User</Button>
              </div>
              <div className="space-y-4">
                {[
                  { name: 'Admin', users: 2, desc: 'Full access to all system modules and billing.' },
                  { name: 'Dispatcher', users: 5, desc: 'Can manage calendar, work orders, and assign technicians.' },
                  { name: 'Technician', users: 24, desc: 'Can view assigned jobs and submit field reports.' },
                ].map(role => (
                  <div key={role.name} className="flex items-center justify-between p-4 border border-surface-200 rounded-lg bg-surface-50">
                    <div>
                      <h4 className="font-semibold text-surface-900">{role.name} <Badge className="ml-2 bg-surface-200 text-surface-700">{role.users} Users</Badge></h4>
                      <p className="text-sm text-surface-500 mt-1">{role.desc}</p>
                    </div>
                    <Button variant="outline" size="sm" className="bg-white">Edit Role</Button>
                  </div>
                ))}
              </div>
            </Card>
          )}

          {/* FALLBACK FOR OTHER TABS */}
          {!['profile', 'system_prefs', 'company_info', 'users'].includes(activeTab) && (
            <Card className="p-12 text-center flex flex-col items-center justify-center border-dashed bg-surface-50/50">
              <SettingsIcon className="w-8 h-8 text-surface-300 mb-3" />
              <h2 className="text-lg font-bold text-surface-900">Configuration Module</h2>
              <p className="text-surface-500 mt-1 max-w-md mx-auto">
                These settings ({activeTab}) are currently managed through your central cloud administration console or are scheduled for upcoming release.
              </p>
            </Card>
          )}

        </main>
      </div>
    </div>
  );
}
