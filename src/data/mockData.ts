import { Activity, Customer, Equipment, InventoryItem, Location, MaintenancePlan, Technician, WorkOrder } from "../types";

export const mockCustomers: Customer[] = [
  { id: 'C-101', name: 'Green Tower Offices', type: 'Commercial', since: '2021-03-15', locationsCount: 3, equipmentCount: 18, activeWorkOrders: 4, status: 'Active' },
  { id: 'C-102', name: 'Nova Logistics', type: 'Industrial', since: '2019-11-02', locationsCount: 1, equipmentCount: 12, activeWorkOrders: 1, status: 'Active' },
  { id: 'C-103', name: 'Sunrise Retail Plaza', type: 'Commercial', since: '2022-06-20', locationsCount: 5, equipmentCount: 34, activeWorkOrders: 2, status: 'Active' },
  { id: 'C-104', name: 'Anderson Residence', type: 'Residential', since: '2023-01-10', locationsCount: 1, equipmentCount: 2, activeWorkOrders: 0, status: 'Active' },
  { id: 'C-105', name: 'TechHub Datacenter', type: 'Commercial', since: '2020-08-05', locationsCount: 2, equipmentCount: 45, activeWorkOrders: 3, status: 'Active' },
];

export const mockLocations: Location[] = [
  { id: 'L-201', customerId: 'C-101', customerName: 'Green Tower Offices', name: 'Headquarters', address: '1420 Business Pkwy, Suite 100', contactName: 'Sarah Jenkins', contactPhone: '(555) 123-4567', equipmentCount: 8, lastVisit: '2026-08-15', nextMaintenance: '2026-09-15' },
  { id: 'L-202', customerId: 'C-101', customerName: 'Green Tower Offices', name: 'Downtown Branch', address: '800 Commerce St, Floor 6', contactName: 'Michael Chang', contactPhone: '(555) 987-6543', equipmentCount: 10, lastVisit: '2026-08-02', nextMaintenance: '2026-10-01' },
  { id: 'L-203', customerId: 'C-102', customerName: 'Nova Logistics', name: 'Main Warehouse', address: '500 Industrial Blvd', contactName: 'Robert Vance', contactPhone: '(555) 444-3333', equipmentCount: 12, lastVisit: '2026-07-20', nextMaintenance: '2026-09-20' },
];

export const mockEquipment: Equipment[] = [
  { id: 'EQ-3001', customerId: 'C-101', locationId: 'L-202', type: 'VRV System', brand: 'Daikin', model: 'VRV IV', serialNumber: 'DXR-492821', installationDate: '2022-03-10', warrantyExpiration: '2027-06-01', status: 'Attention', locationName: 'Green Tower - Floor 6' },
  { id: 'EQ-3002', customerId: 'C-101', locationId: 'L-201', type: 'Rooftop Unit', brand: 'Carrier', model: 'WeatherMaker 48TC', serialNumber: 'CAR-99381A', installationDate: '2021-05-15', warrantyExpiration: '2026-05-15', status: 'Good', locationName: 'Green Tower - HQ Roof' },
  { id: 'EQ-3003', customerId: 'C-102', locationId: 'L-203', type: 'Chiller', brand: 'York', model: 'YVAA', serialNumber: 'YRK-8822001', installationDate: '2019-12-05', warrantyExpiration: '2024-12-05', status: 'Critical', locationName: 'Nova Logistics - Main Plant' },
  { id: 'EQ-3004', customerId: 'C-103', locationId: 'L-205', type: 'Split System', brand: 'Mitsubishi', model: 'P-Series', serialNumber: 'MIT-55611', installationDate: '2023-02-20', warrantyExpiration: '2028-02-20', status: 'Good', locationName: 'Sunrise Plaza - Unit A' },
];

export const mockTechnicians: Technician[] = [
  { id: 'T-001', name: 'Carlos Martinez', email: 'cmartinez@thermoops.com', avatar: 'CM', skills: ['Commercial HVAC', 'VRF Systems', 'Chillers'], status: 'On Site', currentJobId: 'WO-1042', jobsToday: 3, hoursThisWeek: 32.5, rating: 4.9, completionRate: 98 },
  { id: 'T-002', name: 'Sarah O\'Connor', email: 'soconnor@thermoops.com', avatar: 'SO', skills: ['Residential', 'Heat Pumps', 'Ductwork'], status: 'Available', jobsToday: 1, hoursThisWeek: 28, rating: 4.7, completionRate: 95 },
  { id: 'T-003', name: 'David Kim', email: 'dkim@thermoops.com', avatar: 'DK', skills: ['Industrial', 'Chillers', 'Controls'], status: 'In Transit', currentJobId: 'WO-1045', jobsToday: 2, hoursThisWeek: 35, rating: 4.8, completionRate: 96 },
  { id: 'T-004', name: 'Marcus Johnson', email: 'mjohnson@thermoops.com', avatar: 'MJ', skills: ['Commercial HVAC', 'Rooftop Units', 'Preventative'], status: 'On Site', currentJobId: 'WO-1047', jobsToday: 4, hoursThisWeek: 40, rating: 4.6, completionRate: 92 },
  { id: 'T-005', name: 'Elena Rostova', email: 'erostova@thermoops.com', avatar: 'ER', skills: ['Diagnostics', 'Electrical', 'VRF Systems'], status: 'Available', jobsToday: 0, hoursThisWeek: 15, rating: 4.9, completionRate: 99 },
];

export const mockWorkOrders: WorkOrder[] = [
  { id: 'WO-1042', customerId: 'C-101', customerName: 'Green Tower Offices', locationId: 'L-202', locationName: 'Building A - Floor 6', equipmentId: 'EQ-3001', equipmentName: 'Daikin VRV System', technicianId: 'T-001', technicianName: 'Carlos Martinez', serviceType: 'Maintenance', status: 'In Progress', priority: 'Normal', scheduledDate: '2026-08-28', scheduledTime: '09:00', durationHours: 2.5, description: 'Quarterly preventative maintenance on VRV system. Check filters and refrigerant levels.' },
  { id: 'WO-1043', customerId: 'C-102', customerName: 'Nova Logistics', locationId: 'L-203', locationName: 'Main Warehouse', equipmentId: 'EQ-3003', equipmentName: 'York Chiller', technicianId: 'T-003', technicianName: 'David Kim', serviceType: 'Repair', status: 'Scheduled', priority: 'Urgent', scheduledDate: '2026-08-28', scheduledTime: '13:00', durationHours: 3, description: 'Chiller throwing high-pressure fault codes. Immediate inspection required.' },
  { id: 'WO-1044', customerId: 'C-104', customerName: 'Anderson Residence', locationId: 'L-206', locationName: 'Main House', serviceType: 'Installation', status: 'Scheduled', priority: 'Normal', scheduledDate: '2026-08-28', scheduledTime: '08:00', durationHours: 4, description: 'Install new smart thermostat and configure zones.' },
  { id: 'WO-1045', customerId: 'C-103', customerName: 'Sunrise Retail Plaza', locationId: 'L-205', locationName: 'Unit A', equipmentId: 'EQ-3004', equipmentName: 'Mitsubishi Split', technicianId: 'T-004', technicianName: 'Marcus Johnson', serviceType: 'Inspection', status: 'Completed', priority: 'Low', scheduledDate: '2026-08-28', scheduledTime: '14:00', durationHours: 1.5, description: 'Annual safety inspection and duct cleaning.' },
  { id: 'WO-1046', customerId: 'C-105', customerName: 'TechHub Datacenter', locationId: 'L-207', locationName: 'Server Room B', serviceType: 'Repair', status: 'On Hold', priority: 'High', scheduledDate: '2026-08-28', scheduledTime: '10:00', durationHours: 2, description: 'Awaiting specialized compressor part from supplier.' },
  { id: 'WO-1047', customerId: 'C-101', customerName: 'Green Tower Offices', locationId: 'L-201', locationName: 'Headquarters', equipmentId: 'EQ-3002', equipmentName: 'Carrier Rooftop Unit', technicianId: 'T-001', technicianName: 'Carlos Martinez', serviceType: 'Repair', status: 'Scheduled', priority: 'High', scheduledDate: '2026-08-28', scheduledTime: '10:30', durationHours: 1.5, description: 'Fixing rattling noise reported by tenant. (CONFLICT DEMO)' },
  { id: 'WO-1048', customerId: 'C-101', customerName: 'Green Tower Offices', locationId: 'L-201', locationName: 'Headquarters', equipmentId: 'EQ-3002', equipmentName: 'Carrier Rooftop Unit', technicianId: 'T-001', technicianName: 'Carlos Martinez', serviceType: 'Maintenance', status: 'Scheduled', priority: 'Normal', scheduledDate: '2026-08-28', scheduledTime: '14:30', durationHours: 2, description: 'Filter replacement and coil cleaning on rooftop unit.' },
];

export const mockInventory: InventoryItem[] = [
  { id: 'INV-001', partName: 'R410A Refrigerant (25lb)', sku: 'REF-410A-25', category: 'Refrigerants', availableStock: 3, reserved: 2, lowStockThreshold: 5, status: 'Low Stock' },
  { id: 'INV-002', partName: 'Condenser Fan Motor 1/3 HP', sku: 'MTR-CF-33', category: 'Motors', availableStock: 12, reserved: 1, lowStockThreshold: 4, status: 'In Stock' },
  { id: 'INV-003', partName: 'Dual Run Capacitor 45/5 uF', sku: 'CAP-455-440', category: 'Electrical', availableStock: 45, reserved: 5, lowStockThreshold: 10, status: 'In Stock' },
  { id: 'INV-004', partName: 'Air Filter 20x25x1 MERV 11', sku: 'FLT-20251-11', category: 'Filters', availableStock: 150, reserved: 24, lowStockThreshold: 50, status: 'In Stock' },
  { id: 'INV-005', partName: 'Thermostatic Expansion Valve', sku: 'TXV-3TON-R410', category: 'Valves', availableStock: 0, reserved: 2, lowStockThreshold: 3, status: 'Out of Stock' },
];

export const mockMaintenancePlans: MaintenancePlan[] = [
  { id: 'MP-501', customerName: 'Nova Logistics', planName: 'Industrial Annual Agreement', equipmentCount: 12, frequency: 'Every 6 months', nextService: '2026-09-15', status: 'Active' },
  { id: 'MP-502', customerName: 'Green Tower Offices', planName: 'Premium Commercial Care', equipmentCount: 18, frequency: 'Quarterly', nextService: '2026-08-28', status: 'Active' },
  { id: 'MP-503', customerName: 'Sunrise Retail Plaza', planName: 'Basic Preventative', equipmentCount: 34, frequency: 'Annual', nextService: '2027-02-10', status: 'Pending' },
];

export const mockTools = [
  { id: 'TL-101', name: 'Fieldpiece VP67 Vacuum Pump', brand: 'Fieldpiece', category: 'Evacuation', status: 'Assigned', assignedTo: 'Carlos Martinez', lastInspection: '2026-07-15' },
  { id: 'TL-102', name: 'Appion G5Twin Recovery Machine', brand: 'Appion', category: 'Recovery', status: 'Available', lastInspection: '2026-08-01' },
  { id: 'TL-103', name: 'Testo 550s Digital Manifold', brand: 'Testo', category: 'Measurement', status: 'Assigned', assignedTo: 'David Kim', lastInspection: '2026-06-20' },
  { id: 'TL-104', name: 'Fluke 116 HVAC Multimeter', brand: 'Fluke', category: 'Electrical', status: 'Maintenance', lastInspection: '2026-08-25' },
  { id: 'TL-105', name: 'Navac NP4DP Vacuum Pump', brand: 'Navac', category: 'Evacuation', status: 'Available', lastInspection: '2026-07-30' },
];

export const mockReports = [
  { id: 'REP-9081', date: '2026-08-27', customerName: 'Green Tower Offices', locationName: 'Building A - Floor 6', technicianName: 'Carlos Martinez', type: 'Preventative Maintenance', status: 'Signed', amount: 450.00 },
  { id: 'REP-9082', date: '2026-08-27', customerName: 'Nova Logistics', locationName: 'Main Warehouse', technicianName: 'David Kim', type: 'Emergency Repair', status: 'Pending Signature', amount: 1250.00 },
  { id: 'REP-9083', date: '2026-08-26', customerName: 'Sunrise Retail Plaza', locationName: 'Unit A', technicianName: 'Marcus Johnson', type: 'Safety Inspection', status: 'Signed', amount: 200.00 },
  { id: 'REP-9084', date: '2026-08-26', customerName: 'Anderson Residence', locationName: 'Main House', technicianName: 'Sarah O\'Connor', type: 'System Installation', status: 'Draft' },
];

export const mockActivities: Activity[] = [
  { id: 'ACT-1', type: 'WorkOrder', title: 'Work Order WO-1045 Completed', description: 'Technician David Kim marked WO-1045 as completed and uploaded Service Report REP-9082.', timestamp: '2026-08-28T09:15:00Z', relatedId: 'C-102', actor: 'David Kim' },
  { id: 'ACT-2', type: 'System', title: 'Low Inventory Alert', description: 'Refrigerant R-410A stock fell below minimum threshold (15 lbs remaining).', timestamp: '2026-08-28T08:30:00Z', actor: 'System' },
  { id: 'ACT-3', type: 'WorkOrder', title: 'New Work Order Dispatched', description: 'WO-1051 dispatched to Carlos Martinez for Green Tower Offices.', timestamp: '2026-08-28T08:05:00Z', relatedId: 'C-101', actor: 'Dispatcher Sarah' },
  { id: 'ACT-4', type: 'Customer', title: 'Customer Account Updated', description: 'Billing contact updated for Sunrise Retail Plaza.', timestamp: '2026-08-27T16:45:00Z', relatedId: 'C-103', actor: 'Admin' },
  { id: 'ACT-5', type: 'Equipment', title: 'Equipment Status Changed', description: 'Chiller EQ-3003 status changed to Critical by remote telemetry.', timestamp: '2026-08-27T14:20:00Z', relatedId: 'EQ-3003', actor: 'Telemetry Monitor' },
  { id: 'ACT-6', type: 'WorkOrder', title: 'Work Order WO-1042 Started', description: 'Carlos Martinez checked in on-site at Green Tower Offices.', timestamp: '2026-08-28T09:30:00Z', relatedId: 'C-101', actor: 'Carlos Martinez' },
  { id: 'ACT-7', type: 'Equipment', title: 'Maintenance Scheduled', description: 'Annual inspection scheduled for Rooftop Unit EQ-3002.', timestamp: '2026-08-26T10:00:00Z', relatedId: 'EQ-3002', actor: 'Dispatcher Sarah' }
];
