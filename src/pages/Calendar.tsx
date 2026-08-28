import React from 'react';
import { Calendar as CalendarIcon, ChevronLeft, ChevronRight, AlertTriangle, Search } from 'lucide-react';
import { Button, Card } from '../components/ui';
import { mockTechnicians, mockWorkOrders } from '../data/mockData';
import { WorkOrder } from '../types';

export function DispatchCalendar() {
  const START_HOUR = 7; // 7 AM
  const END_HOUR = 18; // 6 PM
  const ROW_HEIGHT = 80; // pixels per hour
  
  const hours = Array.from({ length: END_HOUR - START_HOUR + 1 }, (_, i) => START_HOUR + i);

  // Helper to parse "HH:MM" into decimal hours from START_HOUR
  const getOffsetHours = (timeStr: string) => {
    const [hours, minutes] = timeStr.split(':').map(Number);
    return (hours - START_HOUR) + (minutes / 60);
  };

  // Detect conflicts within a single technician's day
  const getConflictGroups = (orders: WorkOrder[]) => {
    const sorted = [...orders].sort((a, b) => getOffsetHours(a.scheduledTime) - getOffsetHours(b.scheduledTime));
    const conflicts = new Set<string>();
    
    for (let i = 0; i < sorted.length; i++) {
      for (let j = i + 1; j < sorted.length; j++) {
        const startA = getOffsetHours(sorted[i].scheduledTime);
        const endA = startA + sorted[i].durationHours;
        const startB = getOffsetHours(sorted[j].scheduledTime);
        const endB = startB + sorted[j].durationHours;
        
        // If B starts before A ends (overlap)
        if (startB < endA) {
          conflicts.add(sorted[i].id);
          conflicts.add(sorted[j].id);
        }
      }
    }
    return conflicts;
  };

  return (
    <div className="space-y-4 h-[calc(100vh-8rem)] flex flex-col">
      {/* Header Toolbar */}
      <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 flex-shrink-0">
        <div>
          <h1 className="text-2xl font-bold text-surface-900">Dispatch Calendar</h1>
          <p className="text-sm text-surface-500 mt-1">Schedule and monitor field technician assignments.</p>
        </div>
        
        <div className="flex items-center gap-3">
          <div className="flex items-center bg-white border border-surface-200 rounded-lg p-1 shadow-sm">
            <button className="px-3 py-1.5 text-sm font-medium bg-surface-100 rounded-md text-surface-900">Day</button>
            <button className="px-3 py-1.5 text-sm font-medium text-surface-500 hover:text-surface-900">Week</button>
            <button className="px-3 py-1.5 text-sm font-medium text-surface-500 hover:text-surface-900">Month</button>
          </div>
          <div className="h-8 w-px bg-surface-200"></div>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="icon"><ChevronLeft className="w-4 h-4" /></Button>
            <div className="flex items-center gap-2 px-3 py-2 border border-surface-200 bg-white rounded-lg font-medium text-surface-900 text-sm shadow-sm">
              <CalendarIcon className="w-4 h-4 text-surface-500" />
              Today, Aug 28
            </div>
            <Button variant="outline" size="icon"><ChevronRight className="w-4 h-4" /></Button>
          </div>
        </div>
      </div>

      {/* Main Board Container */}
      <Card className="flex-1 overflow-hidden flex flex-col">
        {/* Board Header (Technicians) */}
        <div className="flex border-b border-surface-200 bg-surface-50 flex-shrink-0">
          <div className="w-20 flex-shrink-0 border-r border-surface-200 bg-surface-50 z-10 flex items-center justify-center">
            <span className="text-xs font-bold text-surface-500 uppercase tracking-wider">Time</span>
          </div>
          <div className="flex-1 overflow-x-hidden flex">
            {mockTechnicians.map(tech => (
              <div key={tech.id} className="flex-1 min-w-[220px] border-r border-surface-200 p-3 flex items-center gap-3 bg-white">
                <div className="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center text-xs font-bold text-primary-700">
                  {tech.avatar}
                </div>
                <div>
                  <div className="font-semibold text-sm text-surface-900 leading-tight">{tech.name}</div>
                  <div className="text-[11px] text-surface-500 flex items-center gap-1 mt-0.5">
                    <span className={`w-1.5 h-1.5 rounded-full ${tech.status === 'Available' ? 'bg-success-500' : tech.status === 'On Site' ? 'bg-warning-500' : 'bg-surface-400'}`}></span>
                    {tech.status}
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Board Body (Grid) */}
        <div className="flex-1 overflow-y-auto overflow-x-hidden relative bg-white" style={{ minHeight: '500px' }}>
          <div className="flex min-w-max w-full">
            {/* Time Labels Column */}
            <div className="w-20 flex-shrink-0 border-r border-surface-200 bg-surface-50/50 sticky left-0 z-20">
              {hours.map(hour => (
                <div key={hour} className="text-right pr-3 text-[11px] font-medium text-surface-500 relative" style={{ height: ROW_HEIGHT }}>
                  <span className="absolute top-0 right-3 -mt-2">
                    {hour === 12 ? '12:00 PM' : hour > 12 ? `${hour - 12}:00 PM` : `${hour}:00 AM`}
                  </span>
                </div>
              ))}
            </div>

            {/* Grid Area */}
            <div className="flex-1 relative flex">
              {/* Background horizontal lines */}
              <div className="absolute inset-0 pointer-events-none">
                {hours.map((hour, i) => (
                  <div key={hour} className="border-t border-surface-100 w-full" style={{ height: ROW_HEIGHT, top: i * ROW_HEIGHT }}></div>
                ))}
              </div>

              {/* Technician Lanes */}
              {mockTechnicians.map((tech, tIndex) => {
                const techOrders = mockWorkOrders.filter(wo => wo.technicianId === tech.id);
                const conflicts = getConflictGroups(techOrders);

                return (
                  <div key={tech.id} className="flex-1 min-w-[220px] border-r border-surface-200 relative h-full">
                    {techOrders.map(wo => {
                      const offsetHours = getOffsetHours(wo.scheduledTime);
                      const top = offsetHours * ROW_HEIGHT;
                      const height = wo.durationHours * ROW_HEIGHT;
                      const hasConflict = conflicts.has(wo.id);

                      // Colors based on status
                      const colorMap = {
                        'Scheduled': 'bg-primary-50 border-primary-200 text-primary-900',
                        'In Progress': 'bg-warning-50 border-warning-200 text-warning-900',
                        'Completed': 'bg-success-50 border-success-200 text-success-900',
                        'On Hold': 'bg-surface-100 border-surface-300 text-surface-900',
                        'Cancelled': 'bg-error-50 border-error-200 text-error-900',
                      };
                      const colorClass = colorMap[wo.status];
                      
                      // Highlight border red if conflict
                      const borderClass = hasConflict ? 'border-error-400 shadow-[0_0_0_1px_rgba(239,68,68,1)]' : 'border-l-4 border-l-primary-500';

                      return (
                        <div 
                          key={wo.id}
                          className={`absolute left-1 right-2 rounded-md border p-2 flex flex-col overflow-hidden text-xs cursor-pointer shadow-sm transition-transform hover:scale-[1.02] z-10 ${colorClass} ${borderClass}`}
                          style={{ top: `${top}px`, height: `${height - 2}px` }}
                          title={wo.description}
                        >
                          <div className="flex justify-between items-start mb-1">
                            <span className="font-bold">{wo.id}</span>
                            {hasConflict && <AlertTriangle className="w-3.5 h-3.5 text-error-600" />}
                          </div>
                          <div className="font-semibold truncate">{wo.customerName}</div>
                          <div className="opacity-80 truncate">{wo.serviceType}</div>
                          <div className="mt-auto opacity-70 flex items-center justify-between text-[10px] font-medium pt-1">
                            <span>{wo.scheduledTime} - {wo.durationHours}h</span>
                            <span className="uppercase tracking-wider">{wo.status}</span>
                          </div>
                        </div>
                      );
                    })}
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </Card>
    </div>
  );
}
