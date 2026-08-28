const fs = require('fs');
let content = fs.readFileSync('src/pages/Dashboard.tsx', 'utf8');

// import cn
if (!content.includes("import { cn } from")) {
  content = content.replace("import { formatCurrency }", "import { cn } from '../lib/utils';\nimport { formatCurrency }");
}

// add activeTab state
content = content.replace(
  "const [loading, setLoading] = useState(true);",
  "const [loading, setLoading] = useState(true);\n  const [activeTab, setActiveTab] = useState<'overview' | 'schedule'>('overview');"
);

// update loading skeleton
content = content.replace(
  `<h1 className="text-2xl font-bold text-surface-900 mb-6">Operations Dashboard</h1>`,
  `<div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 className="text-2xl font-bold text-surface-900">Operations Dashboard</h1>
      </div>`
);

// replace h1 in actual view
content = content.replace(
  `<h1 className="text-2xl font-bold text-surface-900 mb-6">Operations Dashboard</h1>`,
  `<div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h1 className="text-2xl font-bold text-surface-900">Operations Dashboard</h1>
        <div className="flex bg-surface-200 p-1 rounded-lg">
           <button onClick={() => setActiveTab('overview')} className={cn("px-4 py-1.5 text-sm font-medium rounded-md transition-colors", activeTab === 'overview' ? "bg-white text-surface-900 shadow-sm" : "text-surface-600 hover:text-surface-900")}>Overview</button>
           <button onClick={() => setActiveTab('schedule')} className={cn("px-4 py-1.5 text-sm font-medium rounded-md transition-colors", activeTab === 'schedule' ? "bg-white text-surface-900 shadow-sm" : "text-surface-600 hover:text-surface-900")}>Today's Schedule</button>
        </div>
      </div>

      {activeTab === 'overview' && (
        <>`
);

// wrap overview content
content = content.replace(
  `{/* Today's Schedule (Mini Dispatch) */}`,
  `  </>
      )}

      {activeTab === 'schedule' && (
        <>
      {/* Today's Schedule (Mini Dispatch) */}`
);

// close schedule wrap
content = content.replace(
  `        </div>
      </Card>
    </div>
  );
}`,
  `        </div>
      </Card>
        </>
      )}
    </div>
  );
}`
);

fs.writeFileSync('src/pages/Dashboard.tsx', content);
