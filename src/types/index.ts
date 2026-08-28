export type WorkOrderStatus = 'Scheduled' | 'In Progress' | 'On Hold' | 'Completed' | 'Cancelled';
export type Priority = 'Low' | 'Normal' | 'High' | 'Urgent';
export type EquipmentStatus = 'Good' | 'Attention' | 'Critical';
export type InventoryStatus = 'In Stock' | 'Low Stock' | 'Out of Stock';

export interface Customer {
  id: string;
  name: string;
  type: 'Commercial' | 'Residential' | 'Industrial';
  since: string;
  locationsCount: number;
  equipmentCount: number;
  activeWorkOrders: number;
  status: 'Active' | 'Inactive';
}

export interface Location {
  id: string;
  customerId: string;
  customerName: string;
  name: string;
  address: string;
  contactName: string;
  contactPhone: string;
  equipmentCount: number;
  lastVisit: string;
  nextMaintenance: string;
}

export interface Equipment {
  id: string;
  customerId: string;
  locationId: string;
  type: string;
  brand: string;
  model: string;
  serialNumber: string;
  installationDate: string;
  warrantyExpiration: string;
  status: EquipmentStatus;
  locationName: string;
}

export interface Technician {
  id: string;
  name: string;
  email: string;
  avatar: string;
  skills: string[];
  status: 'On Site' | 'Available' | 'Off Duty' | 'In Transit';
  currentJobId?: string;
  jobsToday: number;
  hoursThisWeek: number;
  rating: number;
  completionRate: number;
}

export interface WorkOrder {
  id: string;
  customerId: string;
  customerName: string;
  locationId: string;
  locationName: string;
  equipmentId?: string;
  equipmentName?: string;
  technicianId?: string;
  technicianName?: string;
  serviceType: 'Maintenance' | 'Repair' | 'Installation' | 'Inspection';
  status: WorkOrderStatus;
  priority: Priority;
  scheduledDate: string;
  scheduledTime: string;
  durationHours: number;
  description: string;
}

export interface InventoryItem {
  id: string;
  partName: string;
  sku: string;
  category: string;
  availableStock: number;
  reserved: number;
  lowStockThreshold: number;
  status: InventoryStatus;
}

export interface MaintenancePlan {
  id: string;
  customerName: string;
  planName: string;
  equipmentCount: number;
  frequency: string; // e.g. "Every 6 months"
  nextService: string;
  status: 'Active' | 'Expired' | 'Pending';
}

export interface Tool {
  id: string;
  name: string;
  brand: string;
  category: string;
  status: 'Available' | 'Assigned' | 'Maintenance' | 'Out of Service';
  assignedTo?: string;
  lastInspection: string;
}

export interface ServiceReport {
  id: string;
  date: string;
  customerName: string;
  locationName: string;
  technicianName: string;
  type: string;
  status: 'Signed' | 'Pending Signature' | 'Draft';
  amount?: number;
}

export interface Activity {
  id: string;
  type: 'WorkOrder' | 'Equipment' | 'Customer' | 'System';
  title: string;
  description: string;
  timestamp: string;
  relatedId?: string; // e.g. Customer ID, WO ID
  actor?: string; // e.g. "John Doe" or "System"
}

