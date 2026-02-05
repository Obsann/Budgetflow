import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import './Navbar.css';

const Navbar = () => {
    const { user, logout } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        logout();
        navigate('/login');
    };

    return (
        <nav className="navbar">
            <div className="navbar-container">
                <Link to="/" className="navbar-brand">
                    <span className="brand-icon">💰</span>
                    <span className="brand-text">BudgetFlow</span>
                </Link>

                <div className="navbar-links">
                    <Link to="/" className="nav-link">Dashboard</Link>
                    <Link to="/transactions" className="nav-link">Transactions</Link>
                    <Link to="/allocations" className="nav-link">Allocations</Link>
                    <Link to="/reports" className="nav-link">Reports</Link>
                </div>

                <div className="navbar-user">
                    <span className="user-name">👤 {user?.username}</span>
                    <button onClick={handleLogout} className="btn btn-secondary btn-sm">
                        Logout
                    </button>
                </div>
            </div>
        </nav>
    );
};

export default Navbar;
