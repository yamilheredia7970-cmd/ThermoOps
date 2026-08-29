export type WorkOrderStatus = 'Scheduled' | 'In Progress' | 'On Hold' | 'Completed' | 'Cancelled';
export type Priority = 'Low' | 'Normal' | 'High' | 'Urgent';
export type EquipmentStatus = 'Good' | 'Attention' | 'Critical';
export type InventoryStatus = 'In Stock' | 'Low Stock' | 'Out of Stock';

export interface Customer {
  id: number;
  name: string;
  type: 'Commercial' | 'Residential' | 'Industrial';
  since: string;
  locationsCount: number;
  equipmentCount: number;
  activeWorkOrders: number;
  status: 'Active' | 'Inactive';
}

export interface Location {
  id: number;
  customerId: number;
  customerName: string;
  name: string;
  address: string;
  contactName: string | null;
  contactPhone: string | null;
  equipmentCount: number;
  lastVisit: string | null;
  nextMaintenance: string | null;
}

export interface Equipment {
  id: number;
  customerId: number;
  locationId: number;
  type: string;
  brand: string;
  model: string;
  serialNumber: string;
  installationDate: string | null;
  warrantyExpiration: string | null;
  status: EquipmentStatus;
  locationName: string;
}

export interface Technician {
  id: number;
  name: string;
  email: string;
  avatar: string;
  skills: string[];
  status: 'On Site' | 'Available' | 'Off Duty' | 'In Transit';
  currentJobId?: number | null;
  jobsToday: number;
  hoursThisWeek: number;
  rating: number;
  completionRate: number;
}

export interface WorkOrder {
  id: number;
  customerId: number;
  customerName: string;
  locationId: number;
  locationName: string;
  equipmentId?: number | null;
  equipmentName?: string | null;
  technicianId?: number | null;
  technicianName?: string | null;
  serviceType: 'Maintenance' | 'Repair' | 'Installation' | 'Inspection';
  status: WorkOrderStatus;
  priority: Priority;
  scheduledDate: string;
  scheduledTime: string;
  durationHours: number;
  description: string;
}

export interface InventoryItem {
  id: number;
  partName: string;
  sku: string;
  category: string;
  availableStock: number;
  reserved: number;
  lowStockThreshold: number;
  status: InventoryStatus;
}

export interface MaintenancePlan {
  id: number;
  customerId: number;
  customerName: string;
  planName: string;
  equipmentCount: number;
  frequency: string; // e.g. "Every 6 months"
  nextService: string;
  status: 'Active' | 'Expired' | 'Pending';
}

export interface Tool {
  id: number;
  name: string;
  brand: string;
  category: string;
  status: 'Available' | 'Assigned' | 'Maintenance' | 'Out of Service';
  assignedTo?: string | null;
  lastInspection: string | null;
}

export interface ServiceReport {
  id: number;
  workOrderId: number;
  date: string;
  customerName: string;
  locationName: string;
  technicianName: string | null;
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
