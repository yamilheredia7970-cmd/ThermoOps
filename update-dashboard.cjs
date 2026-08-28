const fs = require('fs');

let content = fs.readFileSync('src/pages/Dashboard.tsx', 'utf8');

const targetStr = `            </tbody>
          </table>
        </div>
      </Card>
    </div>
  );
}`;

const mobileViewStr = `            </tbody>
          </table>
        </div>

        {/* Mobile Vertical Cards View */}
        <div className="md:hidden flex flex-col gap-4 p-4 pt-0">
          {mockWorkOrders.map((wo) => {
            const isOverdue = wo.status === 'Scheduled' && parseInt(wo.scheduledTime) < 10;
            return (
              <div key={wo.id + '_mobile'} className="border border-surface-200 rounded-xl p-4 bg-white shadow-sm flex flex-col gap-3">
                <div className="flex justify-between items-start">
                  <div>
                    <h4 className="font-bold text-primary-600 cursor-pointer">{wo.id}</h4>
                    <p className="text-sm font-medium text-surface-900">{wo.serviceType}</p>
                  </div>
                  <Badge variant={
                    wo.status === 'Completed' ? 'success' : 
                    wo.status === 'In Progress' ? 'info' : 
                    isOverdue ? 'error' : 'default'
                  }>
                    {isOverdue ? 'Overdue' : wo.status}
                  </Badge>
                </div>
                <div className="text-sm text-surface-500 flex flex-col gap-2">
                  <div className="flex items-center gap-2">
                     <span className="font-semibold text-surface-700">{wo.scheduledTime}</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <span className="font-medium text-surface-900">{wo.customerName}</span>
                    <span className="text-xs">• {wo.locationName}</span>
                  </div>
                </div>
                <Button variant="outline" className="w-full mt-2 bg-surface-50" size="sm">Quick Update</Button>
              </div>
            );
          })}
        </div>
      </Card>
    </div>
  );
}`;

content = content.replace(targetStr, mobileViewStr);
fs.writeFileSync('src/pages/Dashboard.tsx', content);
