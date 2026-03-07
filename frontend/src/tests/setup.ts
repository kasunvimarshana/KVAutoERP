import '@testing-library/jest-dom';
import { vi } from 'vitest';

// Mock Keycloak
vi.mock('../keycloak', () => ({
  default: {
    init: vi.fn().mockResolvedValue(false),
    login: vi.fn(),
    logout: vi.fn(),
    updateToken: vi.fn().mockResolvedValue(true),
    isTokenExpired: vi.fn().mockReturnValue(false),
    token: null,
    tokenParsed: null,
    onTokenExpired: null,
    onAuthSuccess: null,
    onAuthLogout: null,
  },
}));

// Mock window.matchMedia
Object.defineProperty(window, 'matchMedia', {
  writable: true,
  value: vi.fn().mockImplementation((query: string) => ({
    matches: false,
    media: query,
    onchange: null,
    addListener: vi.fn(),
    removeListener: vi.fn(),
    addEventListener: vi.fn(),
    removeEventListener: vi.fn(),
    dispatchEvent: vi.fn(),
  })),
});
