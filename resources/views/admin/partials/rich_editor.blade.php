@php
    $editorName = $name ?? 'body';
    $editorValue = $value ?? '';
    $editorPlaceholder = $placeholder ?? 'Write here...';
    $editorDisabled = $disabled ?? false;
@endphp

<div class="admin-product-editor-shell admin-post-editor-shell" data-rich-editor>
    <div class="admin-product-editor-menubar">
        <button type="button" class="admin-product-editor-menu-button">File</button>
        <button type="button" class="admin-product-editor-menu-button">Edit</button>
        <button type="button" class="admin-product-editor-menu-button">View</button>
        <button type="button" class="admin-product-editor-menu-button">Insert</button>
        <div class="admin-product-editor-menu-group" data-editor-menu>
            <button
                type="button"
                class="admin-product-editor-menu-button"
                data-menu-trigger
                aria-haspopup="true"
                aria-expanded="false"
            >Format</button>
            <div class="admin-product-editor-dropdown" data-menu-panel hidden>
                <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="bold">
                    <span>Bold</span>
                    <span class="admin-product-editor-shortcut">Ctrl+B</span>
                </button>
                <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="italic">
                    <span>Italic</span>
                    <span class="admin-product-editor-shortcut">Ctrl+I</span>
                </button>
                <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="underline">
                    <span>Underline</span>
                    <span class="admin-product-editor-shortcut">Ctrl+U</span>
                </button>
                <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="strikeThrough">
                    <span>Strikethrough</span>
                </button>
                <div class="admin-product-editor-dropdown-divider"></div>
                <div class="admin-product-editor-menu-group admin-product-editor-menu-group--submenu" data-editor-submenu>
                    <button
                        type="button"
                        class="admin-product-editor-dropdown-item"
                        data-submenu-trigger
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span>Headings</span>
                        <span class="admin-product-editor-caret">&gt;</span>
                    </button>
                    <div class="admin-product-editor-dropdown admin-product-editor-dropdown--submenu" data-submenu-panel hidden>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H1">Heading 1</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H2">Heading 2</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H3">Heading 3</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H4">Heading 4</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H5">Heading 5</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="H6">Heading 6</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-format-block="P">Paragraph</button>
                    </div>
                </div>
                <div class="admin-product-editor-menu-group admin-product-editor-menu-group--submenu" data-editor-submenu>
                    <button
                        type="button"
                        class="admin-product-editor-dropdown-item"
                        data-submenu-trigger
                        aria-haspopup="true"
                        aria-expanded="false"
                    >
                        <span>Align</span>
                        <span class="admin-product-editor-caret">&gt;</span>
                    </button>
                    <div class="admin-product-editor-dropdown admin-product-editor-dropdown--submenu" data-submenu-panel hidden>
                        <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="justifyLeft">Align left</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="justifyCenter">Align center</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="justifyRight">Align right</button>
                        <button type="button" class="admin-product-editor-dropdown-item" data-menu-command="justifyFull">Justify</button>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="admin-product-editor-menu-button">Tools</button>
        <button type="button" class="admin-product-editor-menu-button">Table</button>
    </div>

    <div class="admin-product-editor-toolbar editor-toolbar">
        <button type="button" class="admin-product-editor-icon" data-command="undo" aria-label="Undo">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 14 4 9l5-5"></path><path d="M20 20a8 8 0 0 0-8-8H4"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-command="redo" aria-label="Redo">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 14 5-5-5-5"></path><path d="M4 20a8 8 0 0 1 8-8h8"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon admin-product-editor-icon--text" data-command="bold" aria-label="Bold">B</button>
        <button type="button" class="admin-product-editor-icon admin-product-editor-icon--text admin-product-editor-icon--italic" data-command="italic" aria-label="Italic">I</button>
        <button type="button" class="admin-product-editor-icon" data-command="justifyLeft" aria-label="Align left">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h14"></path><path d="M4 10h10"></path><path d="M4 14h14"></path><path d="M4 18h10"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-command="justifyCenter" aria-label="Align center">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 6h14"></path><path d="M7 10h10"></path><path d="M5 14h14"></path><path d="M7 18h10"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-command="justifyRight" aria-label="Align right">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6h14"></path><path d="M10 10h10"></path><path d="M6 14h14"></path><path d="M10 18h10"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-command="outdent" aria-label="Outdent">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 8H20"></path><path d="M10 12h10"></path><path d="M10 16H20"></path><path d="m4 12 4-4v8l-4-4Z"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-command="indent" aria-label="Indent">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 8h10"></path><path d="M4 12h10"></path><path d="M4 16h10"></path><path d="m20 12-4 4V8l4 4Z"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-action="link" aria-label="Insert link">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 1 0-7l1.5-1.5a5 5 0 0 1 7 7L17 13"></path><path d="M14 11a5 5 0 0 1 0 7l-1.5 1.5a5 5 0 0 1-7-7L7 11"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-action="image" aria-label="Insert image">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><path d="m8 15 3-3 3 3 2-2 4 4"></path><path d="M9 10h.01"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-action="media" aria-label="Insert media">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="14" rx="2"></rect><path d="m10 9 5 3-5 3V9Z"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-action="code" aria-label="Insert code">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 8-4 4 4 4"></path><path d="m15 8 4 4-4 4"></path></svg>
        </button>
        <button type="button" class="admin-product-editor-icon" data-action="fullscreen" aria-label="Fullscreen">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 4H4v4"></path><path d="M16 4h4v4"></path><path d="M20 16v4h-4"></path><path d="M4 16v4h4"></path><path d="m9 9-5-5"></path><path d="m15 9 5-5"></path><path d="m15 15 5 5"></path><path d="m9 15-5 5"></path></svg>
        </button>
    </div>

    <div
        class="admin-product-editor-surface editor-surface"
        data-editor-surface
        data-placeholder="{{ $editorPlaceholder }}"
        contenteditable="{{ $editorDisabled ? 'false' : 'true' }}"
    ></div>

    <textarea class="rich-editor-input" name="{{ $editorName }}" hidden @disabled($editorDisabled)>{{ $editorValue }}</textarea>
</div>
