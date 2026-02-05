import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider, useAuth } from './context/AuthContext';

// Components
import Navbar from './components/Navbar';

// Pages
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import Transactions from './pages/Transactions';
import Allocations from './pages/Allocations';
import Reports from './pages/Reports';

// Protected Route wrapper
const ProtectedRoute = ({ children }) => {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="loading-container">
                <div className="spinner"></div>
            </div>
        );
    }

    return isAuthenticated ? children : <Navigate to="/login" />;
};

// Public Route wrapper (redirect if already authenticated)
const PublicRoute = ({ children }) => {
    const { isAuthenticated, loading } = useAuth();

    if (loading) {
        return (
            <div className="loading-container">
                <div className="spinner"></div>
            </div>
        );
    }

    return !isAuthenticated ? children : <Navigate to="/" />;
};

// Layout with Navbar for authenticated pages
const AuthenticatedLayout = ({ children }) => {
    return (
        <>
            <Navbar />
            <div className="container">
                {children}
            </div>
        </>
    );
};

function App() {
    return (
        <AuthProvider>
            <Router>
                <Routes>
                    {/* Public Routes */}
                    <Route path="/login" element={
                        <PublicRoute>
                            <Login />
                        </PublicRoute>
                    } />
                    <Route path="/register" element={
                        <PublicRoute>
                            <Register />
                        </PublicRoute>
                    } />

                    {/* Protected Routes */}
                    <Route path="/" element={
                        <ProtectedRoute>
                            <AuthenticatedLayout>
                                <Dashboard />
                            </AuthenticatedLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/transactions" element={
                        <ProtectedRoute>
                            <AuthenticatedLayout>
                                <Transactions />
                            </AuthenticatedLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/allocations" element={
                        <ProtectedRoute>
                            <AuthenticatedLayout>
                                <Allocations />
                            </AuthenticatedLayout>
                        </ProtectedRoute>
                    } />
                    <Route path="/reports" element={
                        <ProtectedRoute>
                            <AuthenticatedLayout>
                                <Reports />
                            </AuthenticatedLayout>
                        </ProtectedRoute>
                    } />

                    {/* Fallback */}
                    <Route path="*" element={<Navigate to="/" />} />
                </Routes>
            </Router>
        </AuthProvider>
    );
}

export default App;
