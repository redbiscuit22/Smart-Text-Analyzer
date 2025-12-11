class CustomNavbar extends HTMLElement {
    connectedCallback() {
        this.attachShadow({ mode: 'open' });
        this.shadowRoot.innerHTML = `
            <style>
                nav {
                    background-color: white;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                    padding: 1rem 0;
                }
                
                .dark nav {
                    background-color: #1f2937;
                    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.25), 0 2px 4px -1px rgba(0, 0, 0, 0.15);
                }
                
                .container {
                    max-width: 1280px;
                    margin: 0 auto;
                    padding: 0 1rem;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .logo {
                    font-weight: 700;
                    font-size: 1.5rem;
                    color: #4f46e5;
                    text-decoration: none;
                    display: flex;
                    align-items: center;
                }
                
                .dark .logo {
                    color: #818cf8;
                }
                
                .logo-icon {
                    margin-right: 0.5rem;
                }
                
                .nav-links {
                    display: flex;
                    gap: 1.5rem;
                    align-items: center;
                }
                
                .nav-link {
                    color: #6b7280;
                    text-decoration: none;
                    font-weight: 500;
                    transition: color 0.2s;
                }
                
                .dark .nav-link {
                    color: #9ca3af;
                }
                
                .nav-link:hover {
                    color: #4f46e5;
                }
                
                .dark .nav-link:hover {
                    color: #818cf8;
                }
                
                @media (max-width: 768px) {
                    .nav-links {
                        display: none;
                    }
                }
            </style>
            <nav>
                <div class="container">
                    <a href="/" class="logo">
                        <i data-feather="activity" class="logo-icon"></i>
                        TextSense
                    </a>
                    <div class="nav-links">
                        <a href="#" class="nav-link">Features</a>
                        <a href="#" class="nav-link">Pricing</a>
                        <a href="#" class="nav-link">About</a>
                        <a href="#" class="nav-link">Contact</a>
                    </div>
                </div>
            </nav>
        `;
    }
}

customElements.define('custom-navbar', CustomNavbar);