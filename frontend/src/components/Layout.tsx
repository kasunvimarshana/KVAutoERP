import { Outlet, NavLink, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function Layout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <div style={{ display: 'flex', minHeight: '100vh' }}>
      {/* Sidebar */}
      <aside style={{
        width: 240,
        background: '#1e1b4b',
        color: 'white',
        padding: '1.5rem 0',
        display: 'flex',
        flexDirection: 'column',
      }}>
        <div style={{ padding: '0 1.5rem 1.5rem', borderBottom: '1px solid rgba(255,255,255,0.1)' }}>
          <h1 style={{ fontSize: '1.2rem', fontWeight: 700, color: '#a5b4fc' }}>SaaS Inventory</h1>
          <p style={{ fontSize: '0.75rem', color: '#8b8eb8', marginTop: '0.25rem' }}>
            {user?.name}
          </p>
        </div>

        <nav style={{ flex: 1, padding: '1rem 0' }}>
          {[
            { to: '/dashboard', label: 'Dashboard' },
            { to: '/products', label: 'Products' },
            { to: '/inventory', label: 'Inventory' },
            { to: '/orders', label: 'Orders' },
            { to: '/users', label: 'Users' },
          ].map(({ to, label }) => (
            <NavLink
              key={to}
              to={to}
              style={({ isActive }) => ({
                display: 'block',
                padding: '0.6rem 1.5rem',
                color: isActive ? '#a5b4fc' : '#c7d2fe',
                textDecoration: 'none',
                background: isActive ? 'rgba(165,180,252,0.1)' : 'transparent',
                borderLeft: isActive ? '3px solid #a5b4fc' : '3px solid transparent',
                fontSize: '0.9rem',
              })}
            >
              {label}
            </NavLink>
          ))}
        </nav>

        <div style={{ padding: '1rem 1.5rem', borderTop: '1px solid rgba(255,255,255,0.1)' }}>
          <div style={{ fontSize: '0.75rem', color: '#8b8eb8', marginBottom: '0.5rem' }}>
            Roles: {user?.roles?.join(', ')}
          </div>
          <button
            onClick={handleLogout}
            className="btn btn-secondary"
            style={{ width: '100%', fontSize: '0.85rem' }}
          >
            Logout
          </button>
        </div>
      </aside>

      {/* Main content */}
      <main style={{ flex: 1, padding: '2rem', overflow: 'auto' }}>
        <Outlet />
      </main>
    </div>
  )
}
