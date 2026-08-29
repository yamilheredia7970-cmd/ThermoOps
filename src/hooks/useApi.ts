import { useCallback, useEffect, useState } from 'react';
import { api, ApiCollection, ApiError, ApiResource } from '../lib/api';

interface FetchState<T> {
  data: T | undefined;
  loading: boolean;
  error: string | undefined;
}

/** Fetches a `{ data: [...] }` collection endpoint. */
export function useApiList<T>(path: string | null): FetchState<T[]> & { reload: () => void } {
  const [state, setState] = useState<FetchState<T[]>>({ data: undefined, loading: true, error: undefined });
  const [reloadToken, setReloadToken] = useState(0);

  useEffect(() => {
    if (!path) {
      setState({ data: [], loading: false, error: undefined });
      return;
    }

    let cancelled = false;
    setState(prev => ({ ...prev, loading: true, error: undefined }));

    api
      .get<ApiCollection<T>>(path)
      .then(res => {
        if (!cancelled) setState({ data: res.data, loading: false, error: undefined });
      })
      .catch((err: ApiError) => {
        if (!cancelled) setState({ data: undefined, loading: false, error: err.message });
      });

    return () => {
      cancelled = true;
    };
  }, [path, reloadToken]);

  const reload = useCallback(() => setReloadToken(t => t + 1), []);

  return { ...state, reload };
}

/** Fetches a `{ data: {...} }` single-resource endpoint. */
export function useApiResource<T>(path: string | null): FetchState<T> & { reload: () => void } {
  const [state, setState] = useState<FetchState<T>>({ data: undefined, loading: true, error: undefined });
  const [reloadToken, setReloadToken] = useState(0);

  useEffect(() => {
    if (!path) {
      setState({ data: undefined, loading: false, error: undefined });
      return;
    }

    let cancelled = false;
    setState(prev => ({ ...prev, loading: true, error: undefined }));

    api
      .get<ApiResource<T>>(path)
      .then(res => {
        if (!cancelled) setState({ data: res.data, loading: false, error: undefined });
      })
      .catch((err: ApiError) => {
        if (!cancelled) setState({ data: undefined, loading: false, error: err.message });
      });

    return () => {
      cancelled = true;
    };
  }, [path, reloadToken]);

  const reload = useCallback(() => setReloadToken(t => t + 1), []);

  return { ...state, reload };
}
