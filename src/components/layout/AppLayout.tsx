import React, { useState } from 'react';
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom';
import { 
  LayoutDashboard, ClipboardList, CalendarDays, Users, 
  MapPin, Wind, Wrench, Package, PenTool, FileText, 
  Settings, Bell, Menu, Search, User, Shield, LogOut, Sun, Moon, ChevronRight, X
} from 'lucide-react';
import { cn } from '../../lib/utils';
import { Button } from '../ui';
import { CommandBar } from './CommandBar';
import { useTheme } from '../../contexts/ThemeContext';

const NAV_ITEMS = [
  { group: 'OPERATIONS', items: [
    { name: 'Dashboard', path: '/', icon: LayoutDashboard },
    { name: 'Work Orders', path: '/work-orders', icon: ClipboardList },
    { name: 'Calendar', path: '/calendar', icon: CalendarDays },
  ]},
  { group: 'CUSTOMERS', items: [
    { name: 'Customers', path: '/customers', icon: Users },
    { name: 'Locations', path: '/locations', icon: MapPin },
    { name: 'Equipment', path: '/equipment', icon: Wind },
  ]},
  { group: 'RESOURCES', items: [
    { name: 'Technicians', path: '/technicians', icon: Wrench },
    { name: 'Inventory', path: '/inventory', icon: Package },
    { name: 'Tools', path: '/tools', icon: PenTool },
  ]},
  { group: 'MANAGEMENT', items: [
    { name: 'Reports', path: '/reports', icon: FileText },
  ]},
];

export function Sidebar({ isOpen, onClose }: { isOpen?: boolean, onClose?: () => void }) {
  const location = useLocation();

  return (
    <>
      {/* Mobile overlay */}
      {isOpen && (
        <div className="fixed inset-0 bg-surface-900/50 backdrop-blur-sm z-40 md:hidden" onClick={onClose} />
      )}
      <aside className={cn(
        "fixed inset-y-0 left-0 z-50 w-64 bg-surface-900 text-surface-300 h-screen flex-col flex-shrink-0 overflow-y-auto no-scrollbar print:hidden transition-transform duration-300 md:relative md:translate-x-0 md:flex",
        isOpen ? "translate-x-0 flex" : "-translate-x-full hidden md:flex"
      )}>
      <div className="p-6 flex items-center gap-3">
        <div className="w-8 h-8 rounded-lg bg-primary-500 flex items-center justify-center text-white font-bold text-lg shadow-sm">
          C
        </div>
        <div>
          <h1 className="text-white font-bold tracking-wide text-lg leading-tight">ThermoOps</h1>
          <p className="text-[10px] text-primary-300 font-medium tracking-wider uppercase">HVAC Operations</p>
        </div>
      </div>

      <nav className="flex-1 px-4 pb-8 flex flex-col gap-6">
        {NAV_ITEMS.map((group, idx) => (
          <div key={idx}>
            <h2 className="text-[10px] font-bold text-surface-500 tracking-widest uppercase mb-2 px-3">
              {group.group}
            </h2>
            <div className="flex flex-col gap-1">
              {group.items.map((item) => {
                const Icon = item.icon;
                const isActive = location.pathname === item.path || (item.path !== '/' && location.pathname.startsWith(item.path));
                
                return (
                  <NavLink
                    key={item.name}
                    to={item.path}
                    onClick={() => onClose?.()}
                    className={cn(
                      "flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors",
                      isActive 
                        ? "bg-primary-900/50 text-white" 
                        : "hover:bg-surface-800 hover:text-white"
                    )}
                  >
                    <Icon className={cn("w-4 h-4", isActive ? "text-primary-400" : "text-surface-500")} />
                    {item.name}
                  </NavLink>
                );
              })}
            </div>
          </div>
        ))}
      </nav>
      
      <div className="p-4 mt-auto border-t border-surface-800">
        <NavLink to="/settings" onClick={() => onClose?.()} className="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium hover:bg-surface-800 hover:text-white transition-colors">
          <Settings className="w-4 h-4 text-surface-500" />
          Settings
        </NavLink>
      </div>
    </aside>
    </>
  );
}

export function Header({ onProfileClick, onMenuClick }: { onProfileClick: () => void, onMenuClick: () => void }) {
  return (
    <header className="h-16 bg-white border-b border-surface-200 px-4 md:px-6 flex items-center justify-between sticky top-0 z-20 print:hidden">
      <div className="flex items-center gap-4 flex-1">
        <button onClick={onMenuClick} className="md:hidden text-surface-500 hover:text-surface-900">
          <Menu className="w-5 h-5" />
        </button>
        <div className="hidden md:flex relative w-96">
          <Search className="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-surface-400" />
          <input 
            type="text" 
            placeholder="Search work orders, customers (Ctrl+K)..." 
            className="w-full pl-9 pr-4 py-2 bg-surface-50 border border-surface-200 rounded-lg text-sm cursor-pointer hover:bg-surface-100 transition-all focus:outline-none"
            readOnly
            onClick={() => window.dispatchEvent(new KeyboardEvent('keydown', { key: 'k', ctrlKey: true }))}
          />
          <div className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-1">
            <kbd className="px-1.5 py-0.5 text-[10px] font-semibold text-surface-500 bg-surface-200 rounded border border-surface-300">Ctrl</kbd>
            <kbd className="px-1.5 py-0.5 text-[10px] font-semibold text-surface-500 bg-surface-200 rounded border border-surface-300">K</kbd>
          </div>
        </div>
      </div>
      
      <div className="flex items-center gap-3">
        <button className="relative p-2 text-surface-500 hover:text-surface-900 transition-colors rounded-lg hover:bg-surface-50">
          <Bell className="w-5 h-5" />
          <span className="absolute top-1.5 right-1.5 w-2 h-2 bg-error-500 rounded-full border-2 border-white"></span>
        </button>
        
        <div className="h-8 w-px bg-surface-200 mx-1"></div>
        
        <button onClick={onProfileClick} className="flex items-center gap-3 pl-1 cursor-pointer hover:bg-surface-50 p-1 rounded-lg transition-colors text-left focus:outline-none">
          <div className="text-right hidden md:block">
            <p className="text-sm font-semibold text-surface-900 leading-tight">Admin User</p>
            <p className="text-xs text-surface-500">Dispatcher</p>
          </div>
          <div className="w-9 h-9 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-sm shadow-sm ring-2 ring-transparent focus:ring-primary-500">
            AD
          </div>
        </button>
      </div>
    </header>
  );
}

export function ProfileDrawer({ isOpen, onClose }: { isOpen: boolean, onClose: () => void }) {
  const { theme, toggleTheme } = useTheme();
  const navigate = useNavigate();

  if (!isOpen) return null;

  const navigateTo = (path: string) => {
    navigate(path);
    onClose();
  };

  return (
    <div className="fixed inset-0 z-50 flex sm:justify-end items-end sm:items-stretch">
      <div className="fixed inset-0 bg-surface-900/50 backdrop-blur-sm transition-opacity" onClick={onClose} />
      <div className="relative w-full sm:w-80 bg-white h-[100dvh] sm:h-full shadow-2xl flex flex-col animate-in slide-in-from-bottom sm:slide-in-from-right duration-300">
        
        <div className="p-6 border-b border-surface-100 flex items-center gap-4 relative">
          <button 
            onClick={onClose}
            className="sm:hidden absolute top-4 right-4 p-2 text-surface-400 hover:text-surface-600 hover:bg-surface-100 rounded-full transition-colors"
          >
            <X className="w-5 h-5" />
          </button>
          <div className="w-14 h-14 rounded-full bg-gradient-to-br from-primary-500 to-primary-700 flex items-center justify-center text-white font-bold text-xl shadow-md">
            AD
          </div>
          <div>
            <h2 className="text-lg font-bold text-surface-900">Admin User</h2>
            <p className="text-sm text-surface-500">admin@thermoops.com</p>
            <div className="mt-1 inline-flex px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 text-xs font-semibold">
              Super Admin
            </div>
          </div>
        </div>

        <div className="flex-1 overflow-y-auto p-4 space-y-6">
          
          <div>
            <h3 className="px-3 text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">My Account</h3>
            <div className="space-y-1">
              <button onClick={() => navigateTo('/settings')} className="w-full flex items-center justify-between p-3 rounded-lg hover:bg-surface-50 text-surface-700 hover:text-surface-900 transition-colors">
                <div className="flex items-center gap-3">
                  <User className="w-5 h-5 text-surface-400" />
                  <span className="font-medium text-sm">My Profile</span>
                </div>
                <ChevronRight className="w-4 h-4 text-surface-300" />
              </button>
              <button onClick={() => navigateTo('/settings')} className="w-full flex items-center justify-between p-3 rounded-lg hover:bg-surface-50 text-surface-700 hover:text-surface-900 transition-colors">
                <div className="flex items-center gap-3">
                  <Shield className="w-5 h-5 text-surface-400" />
                  <span className="font-medium text-sm">Account Security</span>
                </div>
                <ChevronRight className="w-4 h-4 text-surface-300" />
              </button>
            </div>
          </div>

          <div>
            <h3 className="px-3 text-xs font-bold text-surface-400 uppercase tracking-wider mb-2">Preferences</h3>
            <div className="space-y-1">
              <button onClick={toggleTheme} className="w-full flex items-center justify-between p-3 rounded-lg hover:bg-surface-50 text-surface-700 hover:text-surface-900 transition-colors">
                <div className="flex items-center gap-3">
                  {theme === 'dark' ? <Moon className="w-5 h-5 text-primary-500" /> : <Sun className="w-5 h-5 text-surface-400" />}
                  <span className="font-medium text-sm">Dark Mode</span>
                </div>
                <div className={`w-10 h-5 rounded-full p-0.5 transition-colors ${theme === 'dark' ? 'bg-primary-500' : 'bg-surface-300'}`}>
                  <div className={`w-4 h-4 rounded-full bg-white transition-transform ${theme === 'dark' ? 'translate-x-5' : 'translate-x-0'}`}></div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div className="p-4 border-t border-surface-100">
          <Button variant="ghost" className="w-full justify-start text-error-600 hover:text-error-700 hover:bg-error-50">
            <LogOut className="w-5 h-5 mr-3" />
            Sign Out
          </Button>
        </div>
      </div>
    </div>
  );
}

export function AppLayout() {
  const [isProfileOpen, setIsProfileOpen] = useState(false);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false); // Mobile sidebar state placeholder

  return (
    <div className="flex min-h-screen bg-surface-50">
      <Sidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} />
      <div className="flex-1 flex flex-col min-w-0">
        <Header onProfileClick={() => setIsProfileOpen(true)} onMenuClick={() => setIsSidebarOpen(true)} />
        <main className="flex-1 p-4 md:p-8 overflow-x-hidden">
          <div className="max-w-7xl mx-auto">
            <Outlet />
          </div>
        </main>
      </div>
      <CommandBar />
      <ProfileDrawer isOpen={isProfileOpen} onClose={() => setIsProfileOpen(false)} />
    </div>
  );
}
