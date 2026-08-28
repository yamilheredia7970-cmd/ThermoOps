import React, { useEffect, useState, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import { Search, ClipboardList, Wind, Users } from 'lucide-react';
import { mockCustomers, mockEquipment, mockWorkOrders } from '../../data/mockData';

export function CommandBar() {
  const [isOpen, setIsOpen] = useState(false);
  const [query, setQuery] = useState('');
  const navigate = useNavigate();
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        setIsOpen((prev) => !prev);
      }
      if (e.key === 'Escape') {
        setIsOpen(false);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);

  useEffect(() => {
    if (isOpen && inputRef.current) {
      inputRef.current.focus();
    } else {
      setQuery('');
    }
  }, [isOpen]);

  if (!isOpen) return null;

  const results = [
    ...mockWorkOrders
      .filter(w => w.id.toLowerCase().includes(query.toLowerCase()) || w.description.toLowerCase().includes(query.toLowerCase()))
      .map(w => ({ id: w.id, title: `${w.id} - ${w.serviceType}`, type: 'WorkOrder', url: `/work-orders`, icon: ClipboardList })),
    ...mockCustomers
      .filter(c => c.name.toLowerCase().includes(query.toLowerCase()) || c.id.toLowerCase().includes(query.toLowerCase()))
      .map(c => ({ id: c.id, title: c.name, type: 'Customer', url: `/customers/${c.id}`, icon: Users })),
    ...mockEquipment
      .filter(e => e.id.toLowerCase().includes(query.toLowerCase()) || e.model.toLowerCase().includes(query.toLowerCase()))
      .map(e => ({ id: e.id, title: `${e.brand} ${e.model}`, type: 'Equipment', url: `/equipment/${e.id}`, icon: Wind }))
  ].slice(0, 8); // Max 8 results

  const handleSelect = (url: string) => {
    navigate(url);
    setIsOpen(false);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-start justify-center pt-32 bg-surface-900/50 backdrop-blur-sm p-4">
      <div className="w-full max-w-2xl bg-white rounded-xl shadow-2xl overflow-hidden" onClick={e => e.stopPropagation()}>
        <div className="flex items-center px-4 py-3 border-b border-surface-200">
          <Search className="w-5 h-5 text-surface-400 mr-3" />
          <input
            ref={inputRef}
            className="flex-1 bg-transparent border-none focus:outline-none text-surface-900 placeholder:text-surface-400"
            placeholder="Search for work orders, customers, or equipment..."
            value={query}
            onChange={(e) => setQuery(e.target.value)}
          />
          <kbd className="hidden sm:inline-block px-2 py-1 text-xs font-semibold text-surface-500 bg-surface-100 rounded">ESC</kbd>
        </div>
        
        {query && (
          <div className="max-h-96 overflow-y-auto">
            {results.length > 0 ? (
              <div className="p-2 space-y-1">
                {results.map((result) => {
                  const Icon = result.icon;
                  return (
                    <button
                      key={result.id}
                      onClick={() => handleSelect(result.url)}
                      className="w-full flex items-center gap-3 px-3 py-2 text-left hover:bg-surface-100 rounded-lg transition-colors group"
                    >
                      <div className="w-8 h-8 rounded bg-surface-50 flex items-center justify-center group-hover:bg-white text-surface-500">
                        <Icon className="w-4 h-4" />
                      </div>
                      <div>
                        <p className="text-sm font-medium text-surface-900">{result.title}</p>
                        <p className="text-xs text-surface-500">{result.type} • {result.id}</p>
                      </div>
                    </button>
                  );
                })}
              </div>
            ) : (
              <div className="p-8 text-center text-surface-500 text-sm">
                No results found for "{query}"
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
