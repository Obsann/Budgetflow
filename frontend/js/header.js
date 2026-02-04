// frontend/js/header.js
// Injects a consistent navigation header into all pages, matching the GitHub header.php structure

function loadHeader() {
    const currentPage = window.location.pathname.split('/').pop() || 'index.html';

    const navLinks = [
        { href: 'index.html', label: 'Dashboard', style: 'link' },
        { href: 'view_all.html', label: 'Transactions', style: 'link' },
        { href: 'create.html', label: 'Add Expense', style: 'button' },
        { href: 'search.html', label: 'Search', style: 'link' },
        { href: 'report.html', label: 'Reports', style: 'link' }
    ];

    const linkClass = 'text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition hover:bg-white/10';
    const activeLinkClass = 'text-white bg-white/10 px-3 py-2 rounded-md text-sm font-medium';
    const buttonClass = 'bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded-full text-sm font-medium transition shadow-lg shadow-blue-500/30';

    const linksHtml = navLinks.map(link => {
        const isActive = link.href === currentPage;
        if (link.style === 'button') {
            return `<a href="${link.href}" class="${buttonClass}">${link.label}</a>`;
        }
        return `<a href="${link.href}" class="${isActive ? activeLinkClass : linkClass}">${link.label}</a>`;
    }).join('\n                        ');

    const headerHtml = `
    <nav class="glass sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="index.html" class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-teal-400 to-blue-500 hover:from-teal-300 hover:to-blue-400 transition">
                        BudgetFlow
                    </a>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        ${linksHtml}
                    </div>
                </div>
                <div>
                   <button id="logout-btn" class="text-red-400 hover:text-red-300 text-sm font-medium transition">Logout</button>
                </div>
            </div>
        </div>
    </nav>
    `;

    // Insert header at the beginning of body
    const placeholder = document.getElementById('header-placeholder');
    if (placeholder) {
        placeholder.innerHTML = headerHtml;
    } else {
        document.body.insertAdjacentHTML('afterbegin', headerHtml);
    }

    // Attach logout handler
    document.getElementById('logout-btn').addEventListener('click', async () => {
        const res = await apiFetch('logout.php');
        if (res.ok) window.location.href = 'login.html';
    });
}

// Auto-load on DOM ready
document.addEventListener('DOMContentLoaded', loadHeader);
