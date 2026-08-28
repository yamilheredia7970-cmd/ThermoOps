import React, { useState } from 'react';
import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import { ThemeProvider } from './contexts/ThemeContext';
import { AppLayout } from './components/layout/AppLayout';
import { Dashboard } from './pages/Dashboard';
import { WorkOrders } from './pages/WorkOrders';
import { Inventory } from './pages/Inventory';
import { Login } from './pages/Login';
import { DispatchCalendar } from './pages/Calendar';
import { Customers } from './pages/Customers';
import { CustomerProfile } from './pages/profiles/CustomerProfile';
import { Locations } from './pages/Locations';
import { Equipment } from './pages/Equipment';
import { EquipmentProfile } from './pages/profiles/EquipmentProfile';
import { Technicians } from './pages/Technicians';
import { TechnicianProfile } from './pages/profiles/TechnicianProfile';
import { Tools } from './pages/Tools';
import { Reports } from './pages/Reports';
import { ReportViewer } from './pages/profiles/ReportViewer';
import { Settings } from './pages/Settings';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  if (!isAuthenticated) {
    return <Login onLogin={() => setIsAuthenticated(true)} />;
  }

  return (
    <ThemeProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<AppLayout />}>
            <Route index element={<Dashboard />} />
            <Route path="work-orders" element={<WorkOrders />} />
            <Route path="calendar" element={<DispatchCalendar />} />
            <Route path="customers" element={<Customers />} />
            <Route path="customers/:id" element={<CustomerProfile />} />
            <Route path="locations" element={<Locations />} />
            <Route path="equipment" element={<Equipment />} />
            <Route path="equipment/:id" element={<EquipmentProfile />} />
            <Route path="technicians" element={<Technicians />} />
            <Route path="technicians/:id" element={<TechnicianProfile />} />
            <Route path="inventory" element={<Inventory />} />
            <Route path="tools" element={<Tools />} />
            <Route path="reports" element={<Reports />} />
            <Route path="reports/:id" element={<ReportViewer />} />
            <Route path="settings" element={<Settings />} />
            <Route path="*" element={<Navigate to="/" replace />} />
          </Route>
        </Routes>
      </BrowserRouter>
    </ThemeProvider>
  );
}

export default App;
