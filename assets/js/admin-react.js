/**
 * Modern React Admin Settings
 *
 * @package AASFWC
 * @since 1.0.0
 */

const { useState, useEffect } = React;

const SettingsApp = () => {
    const [settings, setSettings] = useState({});
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [message, setMessage] = useState('');
    const [activeTab, setActiveTab] = useState('general');

    useEffect(() => {
        loadSettings();
    }, []);

    const loadSettings = async () => {
        try {
            const response = await fetch(aasfwcAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'aasfwc_get_settings',
                    nonce: aasfwcAdmin.nonce
                })
            });
            const data = await response.json();
            if (data.success) {
                setSettings(data.data);
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        } finally {
            setLoading(false);
        }
    };

    const saveSettings = async () => {
        setSaving(true);
        try {
            const response = await fetch(aasfwcAdmin.ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'aasfwc_save_settings',
                    nonce: aasfwcAdmin.nonce,
                    settings: JSON.stringify(settings)
                })
            });
            const data = await response.json();
            if (data.success) {
                setMessage(data.data.message);
                setTimeout(() => setMessage(''), 3000);
            }
        } catch (error) {
            console.error('Error saving settings:', error);
        } finally {
            setSaving(false);
        }
    };

    const updateSetting = (key, value) => {
        setSettings(prev => ({ ...prev, [key]: value }));
    };

    if (loading) {
        return React.createElement('div', { className: 'min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center' },
            React.createElement('div', { className: 'text-center' },
                React.createElement('div', { className: 'animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600 mx-auto mb-4' }),
                React.createElement('p', { className: 'text-slate-600 font-medium' }, 'Loading settings...')
            )
        );
    }

    return React.createElement('div', { className: 'min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50' },
        React.createElement('div', { className: 'bg-white shadow-sm border-b border-slate-200' },
            React.createElement('div', { className: 'max-w-7xl mx-auto px-6 py-8' },
                React.createElement('h1', { className: 'text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2' }, aasfwcAdmin.strings.title),
                React.createElement('p', { className: 'text-slate-600 text-lg' }, 'Configure your AJAX search with intelligent features')
            )
        ),

        React.createElement('div', { className: 'max-w-7xl mx-auto px-6 py-8' },
            message && React.createElement('div', { className: 'mb-8 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm' },
                React.createElement('p', { className: 'text-green-800 font-medium' }, message)
            ),

            React.createElement('div', { className: 'mb-8' },
                React.createElement('div', { className: 'border-b border-slate-200 bg-white rounded-t-xl' },
                    React.createElement('nav', { className: 'flex space-x-8 px-6' },
                        React.createElement('button', {
                            onClick: () => setActiveTab('general'),
                            className: activeTab === 'general' ? 'py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm' : 'py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm'
                        }, '⚙️ General Settings'),
                        React.createElement('button', {
                            onClick: () => setActiveTab('display'),
                            className: activeTab === 'display' ? 'py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm' : 'py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm'
                        }, '🎨 Display Options'),
                        React.createElement('button', {
                            onClick: () => setActiveTab('ai'),
                            className: activeTab === 'ai' ? 'py-4 px-1 border-b-2 border-blue-500 text-blue-600 font-medium text-sm' : 'py-4 px-1 border-b-2 border-transparent text-slate-500 hover:text-slate-700 font-medium text-sm'
                        }, '🤖 AI Features')
                    )
                )
            ),

            React.createElement('div', { className: 'bg-white rounded-b-xl shadow-sm border border-slate-200' },
                activeTab === 'general' && React.createElement('div', { className: 'p-8' },
                    React.createElement('div', { className: 'grid grid-cols-1 lg:grid-cols-2 gap-8' },
                        React.createElement('div', { className: 'space-y-8' },
                            React.createElement('div', { className: 'bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100' },
                                React.createElement('h3', { className: 'text-lg font-semibold text-slate-900 mb-2' }, '🚀 AJAX Search'),
                                React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, 'Enable real-time search functionality'),
                                React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer' },
                                    React.createElement('input', {
                                        type: 'checkbox',
                                        checked: settings.enable_ajax || false,
                                        onChange: (e) => updateSetting('enable_ajax', e.target.checked),
                                        className: 'sr-only peer'
                                    }),
                                    React.createElement('div', { className: 'relative w-14 h-7 bg-slate-200 rounded-full peer peer-checked:bg-blue-600' },
                                        React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full transition-transform peer-checked:translate-x-7' })
                                    )
                                )
                            ),
                            React.createElement('div', { className: 'bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-100' },
                                React.createElement('h3', { className: 'text-lg font-semibold text-slate-900 mb-2' }, '🤖 AI-Powered Search'),
                                React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, 'Enhanced search with intelligent algorithms'),
                                React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer' },
                                    React.createElement('input', {
                                        type: 'checkbox',
                                        checked: settings.enable_ai || false,
                                        onChange: (e) => updateSetting('enable_ai', e.target.checked),
                                        className: 'sr-only peer'
                                    }),
                                    React.createElement('div', { className: 'relative w-14 h-7 bg-slate-200 rounded-full peer peer-checked:bg-purple-600' },
                                        React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full transition-transform peer-checked:translate-x-7' })
                                    )
                                )
                            )
                        ),
                        React.createElement('div', { className: 'space-y-6' },
                            React.createElement('div', { className: 'bg-white rounded-xl p-6 border border-slate-200 shadow-sm' },
                                React.createElement('label', { className: 'block text-sm font-semibold text-slate-700 mb-3' }, '📊 Search Results Limit'),
                                React.createElement('input', {
                                    type: 'number',
                                    min: 1,
                                    max: 50,
                                    value: settings.search_limit || 10,
                                    onChange: (e) => updateSetting('search_limit', parseInt(e.target.value)),
                                    className: 'w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                                }),
                                React.createElement('p', { className: 'text-sm text-slate-500 mt-2' }, 'Maximum number of results to display (1-50)')
                            ),
                            React.createElement('div', { className: 'bg-white rounded-xl p-6 border border-slate-200 shadow-sm' },
                                React.createElement('label', { className: 'block text-sm font-semibold text-slate-700 mb-3' }, '✏️ Minimum Characters'),
                                React.createElement('input', {
                                    type: 'number',
                                    min: 1,
                                    max: 5,
                                    value: settings.min_chars || 2,
                                    onChange: (e) => updateSetting('min_chars', parseInt(e.target.value)),
                                    className: 'w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                                }),
                                React.createElement('p', { className: 'text-sm text-slate-500 mt-2' }, 'Characters needed to trigger search (1-5)')
                            ),
                            React.createElement('div', { className: 'bg-white rounded-xl p-6 border border-slate-200 shadow-sm' },
                                React.createElement('label', { className: 'block text-sm font-semibold text-slate-700 mb-3' }, '⏱️ Search Delay (ms)'),
                                React.createElement('input', {
                                    type: 'number',
                                    min: 100,
                                    max: 1000,
                                    step: 100,
                                    value: settings.search_delay || 300,
                                    onChange: (e) => updateSetting('search_delay', parseInt(e.target.value)),
                                    className: 'w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500'
                                }),
                                React.createElement('p', { className: 'text-sm text-slate-500 mt-2' }, 'Debounce delay in milliseconds (100-1000)')
                            )
                        )
                    )
                ),

                activeTab === 'display' && React.createElement('div', { className: 'p-8' },
                    React.createElement('div', { className: 'grid grid-cols-1 md:grid-cols-3 gap-6' },
                        React.createElement('div', { className: 'bg-gradient-to-br from-emerald-50 to-teal-50 rounded-xl p-6 border border-emerald-100' },
                            React.createElement('h3', { className: 'font-semibold text-slate-900 mb-2' }, '🖼️ Product Images'),
                            React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, 'Show thumbnails'),
                            React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer' },
                                React.createElement('input', {
                                    type: 'checkbox',
                                    checked: settings.show_images || false,
                                    onChange: (e) => updateSetting('show_images', e.target.checked),
                                    className: 'sr-only peer'
                                }),
                                React.createElement('div', { className: 'relative w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-emerald-600' },
                                    React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5' })
                                )
                            )
                        ),
                        React.createElement('div', { className: 'bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl p-6 border border-amber-100' },
                            React.createElement('h3', { className: 'font-semibold text-slate-900 mb-2' }, '💰 Product Prices'),
                            React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, 'Display pricing'),
                            React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer' },
                                React.createElement('input', {
                                    type: 'checkbox',
                                    checked: settings.show_price || false,
                                    onChange: (e) => updateSetting('show_price', e.target.checked),
                                    className: 'sr-only peer'
                                }),
                                React.createElement('div', { className: 'relative w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-amber-600' },
                                    React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5' })
                                )
                            )
                        ),
                        React.createElement('div', { className: 'bg-gradient-to-br from-rose-50 to-pink-50 rounded-xl p-6 border border-rose-100' },
                            React.createElement('h3', { className: 'font-semibold text-slate-900 mb-2' }, '🛒 Add to Cart'),
                            React.createElement('p', { className: 'text-sm text-slate-600 mb-4' }, 'Quick purchase'),
                            React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer' },
                                React.createElement('input', {
                                    type: 'checkbox',
                                    checked: settings.show_add_to_cart || false,
                                    onChange: (e) => updateSetting('show_add_to_cart', e.target.checked),
                                    className: 'sr-only peer'
                                }),
                                React.createElement('div', { className: 'relative w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-rose-600' },
                                    React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full transition-transform peer-checked:translate-x-5' })
                                )
                            )
                        )
                    )
                ),

                activeTab === 'ai' && React.createElement('div', { className: 'p-8' },
                    React.createElement('div', { className: 'grid grid-cols-1 md:grid-cols-2 gap-8' },
                        React.createElement('div', { className: 'bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl p-8 border border-violet-100' },
                            React.createElement('h3', { className: 'text-xl font-semibold text-slate-900 mb-2' }, '✨ Typo Correction'),
                            React.createElement('p', { className: 'text-slate-600 mb-4' }, 'Automatically correct common spelling mistakes'),
                            React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer mb-4' },
                                React.createElement('input', {
                                    type: 'checkbox',
                                    checked: settings.enable_typo_correction || false,
                                    onChange: (e) => updateSetting('enable_typo_correction', e.target.checked),
                                    className: 'sr-only peer'
                                }),
                                React.createElement('div', { className: 'relative w-14 h-7 bg-slate-200 rounded-full peer peer-checked:bg-violet-600' },
                                    React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full transition-transform peer-checked:translate-x-7' })
                                )
                            ),
                            React.createElement('div', { className: 'bg-white rounded-lg p-4' },
                                React.createElement('p', { className: 'text-sm text-slate-700 font-medium mb-2' }, 'Examples:'),
                                React.createElement('ul', { className: 'text-sm text-slate-600 space-y-1' },
                                    React.createElement('li', {}, '• "tshirt" → "t-shirt"'),
                                    React.createElement('li', {}, '• "shose" → "shoes"'),
                                    React.createElement('li', {}, '• "jens" → "jeans"')
                                )
                            )
                        ),
                        React.createElement('div', { className: 'bg-gradient-to-br from-cyan-50 to-blue-50 rounded-xl p-8 border border-cyan-100' },
                            React.createElement('h3', { className: 'text-xl font-semibold text-slate-900 mb-2' }, '🔄 Synonym Support'),
                            React.createElement('p', { className: 'text-slate-600 mb-4' }, 'Expand searches with related terms'),
                            React.createElement('label', { className: 'relative inline-flex items-center cursor-pointer mb-4' },
                                React.createElement('input', {
                                    type: 'checkbox',
                                    checked: settings.enable_synonyms || false,
                                    onChange: (e) => updateSetting('enable_synonyms', e.target.checked),
                                    className: 'sr-only peer'
                                }),
                                React.createElement('div', { className: 'relative w-14 h-7 bg-slate-200 rounded-full peer peer-checked:bg-cyan-600' },
                                    React.createElement('div', { className: 'absolute top-0.5 left-0.5 w-6 h-6 bg-white rounded-full transition-transform peer-checked:translate-x-7' })
                                )
                            ),
                            React.createElement('div', { className: 'bg-white rounded-lg p-4' },
                                React.createElement('p', { className: 'text-sm text-slate-700 font-medium mb-2' }, 'Examples:'),
                                React.createElement('ul', { className: 'text-sm text-slate-600 space-y-1' },
                                    React.createElement('li', {}, '• "shirt" includes "top", "blouse"'),
                                    React.createElement('li', {}, '• "pants" includes "trousers"'),
                                    React.createElement('li', {}, '• "shoes" includes "footwear"')
                                )
                            )
                        )
                    )
                )
            ),

            React.createElement('div', { className: 'fixed bottom-8 right-8 z-50' },
                React.createElement('button', {
                    onClick: saveSettings,
                    disabled: saving,
                    className: 'px-8 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200'
                },
                    saving ? 'Saving...' : 'Save Settings 💾'
                )
            )
        )
    );
};

// Initialize React app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('aasfwc-settings-root');
    if (root) {
        ReactDOM.render(React.createElement(SettingsApp), root);
    }
});