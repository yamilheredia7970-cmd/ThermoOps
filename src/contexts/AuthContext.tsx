import React, { createContext, useContext, useEffect, useState, useCallback } from 'react';
import { api, ApiResource } from '../lib/api';

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  avatar: string;
  status: 'active' | 'inactive';
  roles: string[];
}

interface AuthContextType {
  user: AuthUser | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  hasRole: (...roles: string[]) => boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [user, setUser] = useState<AuthUser | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    api
      .get<ApiResource<AuthUser>>('/me')
      .then(res => setUser(res.data))
      .catch(() => setUser(null))
      .finally(() => setIsLoading(false));
  }, []);

  const login = useCallback(async (email: string, password: string) => {
    const res = await api.post<ApiResource<AuthUser>>('/login', { email, password });
    setUser(res.data);
  }, []);

  const logout = useCallback(async () => {
    try {
      await api.post('/logout');
    } finally {
      setUser(null);
    }
  }, []);

  const hasRole = useCallback((...roles: string[]) => !!user && roles.some(r => user.roles.includes(r)), [user]);

  return (
    <AuthContext.Provider value={{ user, isLoading, login, logout, hasRole }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
}
