// Thin fetch wrapper around the Laravel API. Requests go through Vite's dev
// proxy (see vite.config.ts) so the browser sees everything as same-origin,
// which lets Sanctum's SPA cookie auth work without a bearer token.

export class ApiError extends Error {
  status: number;
  errors: Record<string, string[]>;

  constructor(status: number, message: string, errors: Record<string, string[]> = {}) {
    super(message);
    this.status = status;
    this.errors = errors;
  }

  /** First validation message for a field, if any. */
  fieldError(field: string): string | undefined {
    return this.errors[field]?.[0];
  }
}

function readCookie(name: string): string | null {
  const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`));
  return match ? decodeURIComponent(match[1]) : null;
}

async function ensureCsrfCookie(): Promise<void> {
  if (readCookie('XSRF-TOKEN')) return;
  await fetch('/sanctum/csrf-cookie', { credentials: 'include' });
}

interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'PATCH' | 'DELETE';
  body?: unknown;
  /** Pass a FormData body as-is (file uploads) instead of JSON-encoding it. */
  isFormData?: boolean;
}

async function request<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const method = options.method ?? 'GET';

  if (method !== 'GET') {
    await ensureCsrfCookie();
  }

  const headers: Record<string, string> = { Accept: 'application/json' };
  const xsrfToken = readCookie('XSRF-TOKEN');
  if (xsrfToken && method !== 'GET') {
    headers['X-XSRF-TOKEN'] = xsrfToken;
  }

  let body: BodyInit | undefined;
  if (options.body instanceof FormData) {
    body = options.body;
  } else if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json';
    body = JSON.stringify(options.body);
  }

  const response = await fetch(`/api/v1${path}`, {
    method,
    headers,
    body,
    credentials: 'include',
  });

  if (response.status === 204) {
    return undefined as T;
  }

  const isJson = response.headers.get('content-type')?.includes('application/json');
  const payload = isJson ? await response.json() : undefined;

  if (!response.ok) {
    throw new ApiError(
      response.status,
      payload?.message ?? `Request failed with status ${response.status}`,
      payload?.errors ?? {}
    );
  }

  return payload as T;
}

export const api = {
  get: <T>(path: string) => request<T>(path),
  post: <T>(path: string, body?: unknown) => request<T>(path, { method: 'POST', body }),
  put: <T>(path: string, body?: unknown) => request<T>(path, { method: 'PUT', body }),
  delete: <T>(path: string) => request<T>(path, { method: 'DELETE' }),
  upload: <T>(path: string, formData: FormData) =>
    request<T>(path, { method: 'POST', body: formData, isFormData: true }),
};

/** Unwraps Laravel's `{ data: [...] }` collection envelope. */
export interface ApiCollection<T> {
  data: T[];
}

/** Unwraps Laravel's `{ data: {...} }` single-resource envelope. */
export interface ApiResource<T> {
  data: T;
}
