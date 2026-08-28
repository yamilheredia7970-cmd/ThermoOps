const fs = require('fs');

let content = fs.readFileSync('src/components/layout/AppLayout.tsx', 'utf8');

// Update Sidebar props
content = content.replace(
  `export function Sidebar() {
  const location = useLocation();

  return (
    <aside className="hidden md:flex w-64 bg-surface-900 text-surface-300 h-screen flex-col flex-shrink-0 sticky top-0 overflow-y-auto no-scrollbar print:hidden">`,
  `export function Sidebar({ isOpen, onClose }: { isOpen?: boolean, onClose?: () => void }) {
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
      )}>`
);

// Close the Sidebar on navigation
content = content.replace(
  `to={item.path}`,
  `to={item.path}
                    onClick={() => onClose?.()}`
);
content = content.replace(
  `to="/settings" className="`,
  `to="/settings" onClick={() => onClose?.()} className="`
);

// Update AppLayout to use the new props
content = content.replace(
  `<Sidebar />`,
  `<Sidebar isOpen={isSidebarOpen} onClose={() => setIsSidebarOpen(false)} />`
);

fs.writeFileSync('src/components/layout/AppLayout.tsx', content);
