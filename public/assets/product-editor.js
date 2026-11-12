(() => {
    const editors = document.querySelectorAll('[data-rich-editor]');
    const activeButtonClasses = ['bg-blue-50', 'text-blue-700', 'border-blue-200'];
    const inactiveButtonClasses = ['bg-slate-50', 'text-slate-700', 'border-slate-200'];
    const fullscreenClasses = ['fixed', 'inset-3', 'z-[999]', 'rounded-[1.75rem]', 'shadow-2xl'];

    const normalize = (html) => html
        .replace(/<div><br><\/div>/gi, '')
        .replace(/&nbsp;/gi, ' ')
        .trim();

    const escapeHtml = (value) => value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const updateButtonStates = (container) => {
        container.querySelectorAll('[data-command]').forEach((button) => {
            const command = button.dataset.command;
            if (!command) {
                return;
            }

            try {
                const isActive = document.queryCommandState(command);
                button.classList.toggle('is-active', isActive);
                button.classList.toggle(activeButtonClasses[0], isActive);
                button.classList.toggle(activeButtonClasses[1], isActive);
                button.classList.toggle(activeButtonClasses[2], isActive);
                button.classList.toggle(inactiveButtonClasses[0], !isActive);
                button.classList.toggle(inactiveButtonClasses[1], !isActive);
                button.classList.toggle(inactiveButtonClasses[2], !isActive);
            } catch (error) {
                button.classList.remove('is-active');
                button.classList.remove(...activeButtonClasses);
                button.classList.add(...inactiveButtonClasses);
            }
        });
    };

    editors.forEach((container) => {
        const input = container.querySelector('.rich-editor-input');
        const surface = container.querySelector('[data-editor-surface]');
        const form = container.closest('form');
        let savedRange = null;

        if (!(input instanceof HTMLTextAreaElement) || !(surface instanceof HTMLElement)) {
            return;
        }

        const isNodeInsideSurface = (node) => {
            if (!node) {
                return false;
            }

            return surface.contains(node.nodeType === Node.TEXT_NODE ? node.parentNode : node);
        };

        const saveSelection = () => {
            const selection = window.getSelection();
            if (!selection || selection.rangeCount === 0) {
                return;
            }

            const range = selection.getRangeAt(0);
            if (!isNodeInsideSurface(range.commonAncestorContainer)) {
                return;
            }

            savedRange = range.cloneRange();
        };

        const restoreSelection = () => {
            if (!savedRange) {
                surface.focus();
                return;
            }

            const selection = window.getSelection();
            if (!selection) {
                surface.focus();
                return;
            }

            surface.focus();
            selection.removeAllRanges();
            selection.addRange(savedRange);
        };

        const syncToInput = () => {
            input.value = normalize(surface.innerHTML);
            updateButtonStates(container);
        };

        const insertHtml = (html) => {
            restoreSelection();
            document.execCommand('insertHTML', false, html);
            saveSelection();
            syncToInput();
        };

        const runCommand = (command, value = null) => {
            restoreSelection();
            document.execCommand(command, false, value);
            saveSelection();
            syncToInput();
        };

        const formatBlock = (tagName) => {
            const normalizedTag = String(tagName).toLowerCase();
            runCommand('formatBlock', `<${normalizedTag}>`);
        };

        const closeSubmenus = (menuPanel) => {
            menuPanel.querySelectorAll('[data-editor-submenu]').forEach((submenu) => {
                const trigger = submenu.querySelector('[data-submenu-trigger]');
                const panel = submenu.querySelector('[data-submenu-panel]');
                if (!(trigger instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                    return;
                }

                trigger.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
            });
        };

        const closeMenus = () => {
            container.querySelectorAll('[data-editor-menu]').forEach((menuGroup) => {
                const trigger = menuGroup.querySelector('[data-menu-trigger]');
                const panel = menuGroup.querySelector('[data-menu-panel]');
                if (!(trigger instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                    return;
                }

                trigger.setAttribute('aria-expanded', 'false');
                panel.hidden = true;
                closeSubmenus(panel);
            });
        };

        surface.innerHTML = input.value || '';

        container.querySelectorAll('[data-command]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const command = button.dataset.command;
                if (!command) {
                    return;
                }

                runCommand(command);
            });
        });

        container.querySelectorAll('[data-action="link"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const href = window.prompt('Enter a URL');
                if (!href) {
                    return;
                }

                runCommand('createLink', href);
            });
        });

        container.querySelectorAll('[data-action="clear"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                surface.innerHTML = '';
                syncToInput();
            });
        });

        container.querySelectorAll('[data-action="image"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const src = window.prompt('Enter an image URL');
                if (!src) {
                    return;
                }

                const alt = window.prompt('Enter image alt text') || '';
                insertHtml(`<p><img src="${escapeHtml(src)}" alt="${escapeHtml(alt)}"></p>`);
            });
        });

        container.querySelectorAll('[data-action="media"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const href = window.prompt('Enter a media URL');
                if (!href) {
                    return;
                }

                insertHtml(`<p><a href="${escapeHtml(href)}">Media link</a></p>`);
            });
        });

        container.querySelectorAll('[data-action="code"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                const selection = window.getSelection();
                const selectedText = selection ? selection.toString() : '';
                const content = selectedText || 'Code snippet';

                insertHtml(`<pre><code>${escapeHtml(content)}</code></pre>`);
            });
        });

        container.querySelectorAll('[data-action="fullscreen"]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
            button.addEventListener('click', () => {
                container.classList.toggle('is-fullscreen');
                fullscreenClasses.forEach((className) => container.classList.toggle(className));
                document.body.classList.toggle('overflow-hidden');
                surface.focus();
            });
        });

        container.querySelectorAll('[data-menu-trigger], [data-submenu-trigger], [data-menu-command], [data-format-block]').forEach((button) => {
            button.addEventListener('mousedown', (event) => event.preventDefault());
        });

        container.querySelectorAll('[data-editor-menu]').forEach((menuGroup) => {
            const trigger = menuGroup.querySelector('[data-menu-trigger]');
            const panel = menuGroup.querySelector('[data-menu-panel]');

            if (!(trigger instanceof HTMLElement) || !(panel instanceof HTMLElement)) {
                return;
            }

            trigger.addEventListener('click', () => {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';
                closeMenus();
                if (!isOpen) {
                    trigger.setAttribute('aria-expanded', 'true');
                    panel.hidden = false;
                }
            });

            panel.querySelectorAll('[data-editor-submenu]').forEach((submenu) => {
                const submenuTrigger = submenu.querySelector('[data-submenu-trigger]');
                const submenuPanel = submenu.querySelector('[data-submenu-panel]');

                if (!(submenuTrigger instanceof HTMLElement) || !(submenuPanel instanceof HTMLElement)) {
                    return;
                }

                submenuTrigger.addEventListener('click', () => {
                    const isOpen = submenuTrigger.getAttribute('aria-expanded') === 'true';

                    panel.querySelectorAll('[data-editor-submenu]').forEach((otherSubmenu) => {
                        const otherTrigger = otherSubmenu.querySelector('[data-submenu-trigger]');
                        const otherPanel = otherSubmenu.querySelector('[data-submenu-panel]');

                        if (!(otherTrigger instanceof HTMLElement) || !(otherPanel instanceof HTMLElement)) {
                            return;
                        }

                        otherTrigger.setAttribute('aria-expanded', 'false');
                        otherPanel.hidden = true;
                    });

                    if (!isOpen) {
                        submenuTrigger.setAttribute('aria-expanded', 'true');
                        submenuPanel.hidden = false;
                    }
                });
            });
        });

        container.querySelectorAll('[data-menu-command]').forEach((button) => {
            button.addEventListener('click', () => {
                const command = button.getAttribute('data-menu-command');
                if (!command) {
                    return;
                }

                runCommand(command);
                closeMenus();
            });
        });

        container.querySelectorAll('[data-format-block]').forEach((button) => {
            button.addEventListener('click', () => {
                const tagName = button.getAttribute('data-format-block');
                if (!tagName) {
                    return;
                }

                formatBlock(tagName);
                closeMenus();
            });
        });

        surface.addEventListener('input', syncToInput);
        surface.addEventListener('keyup', () => {
            saveSelection();
            updateButtonStates(container);
        });
        surface.addEventListener('mouseup', () => {
            saveSelection();
            updateButtonStates(container);
        });
        surface.addEventListener('blur', syncToInput);
        surface.addEventListener('focus', saveSelection);

        document.addEventListener('click', (event) => {
            if (!(event.target instanceof Node) || container.contains(event.target)) {
                return;
            }

            closeMenus();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeMenus();
            }
        });

        if (form) {
            form.addEventListener('submit', syncToInput);
        }

        syncToInput();
    });
})();
