# 🤖 BankBot - AI Islamic Banking Assistant

**An advanced AI-powered chatbot for WordPress with multi-language support, real-time streaming, comprehensive analytics, and anti-spam protection.**

![Version](https://img.shields.io/badge/version-3.2.0-blue.svg)
![WordPress](https://img.shields.io/badge/wordpress-5.0%2B-brightgreen.svg)
![PHP](https://img.shields.io/badge/php-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0-orange.svg)

---

## 📋 Table of Contents

- [Features](#-features)
- [Screenshots](#-screenshots)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Configuration](#-configuration)
- [API Integration](#-api-integration)
- [Customization](#-customization)
- [Analytics Dashboard](#-analytics-dashboard)
- [Anti-Spam Protection](#-anti-spam-protection)
- [Multi-Language Support](#-multi-language-support)
- [Shortcodes](#-shortcodes)
- [FAQs](#-faqs)
- [Troubleshooting](#-troubleshooting)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)
- [Credits](#-credits)
- [License](#-license)

---

## ✨ Features

### 🤖 **AI-Powered Chatbot**
- **Real-Time Streaming** - Typewriter effect for realistic conversations
- **Multi-Model Support** - OpenAI, Mistral, Claude integration
- **Smart Context** - Custom training data for products, services, and policies
- **Demo Mode** - Offline smart responses for testing
- **Conversation History** - Context-aware multi-turn conversations
- **Natural Language Processing** - Understands complex queries

### 🌍 **Multi-Language Support**
- **English (LTR)** - Left-to-right text direction
- **Arabic (RTL)** - Right-to-left text direction
- **Custom Messages** - Separate welcome messages per language
- **Language Switching** - Easy language configuration
- **Unicode Support** - Proper handling of Arabic characters

### 🎨 **Advanced Customization**
- **Complete Theme Control** - 13+ color options
- **Layout Flexibility** - 4 positioning options (corners)
- **Typography Options** - Font families, sizes, weights
- **Icon Customization** - Emoji, text, or custom images
- **Responsive Design** - Mobile-optimized interface
- **Shadow & Animation** - Customizable effects

### 📊 **Analytics Dashboard**
- **Real-Time Tracking** - Live conversation monitoring
- **Visual Charts** - Interactive trend visualization
- **Common Queries** - Most asked questions analysis
- **Date Filtering** - Custom date range analytics
- **User Insights** - IP tracking and hostname detection
- **Export/Import** - CSV data management

### 🛡️ **Anti-Spam Protection**
- **Rate Limiting** - Configurable messages per time window
- **IP Blacklist** - Block specific IP addresses
- **Spam Detection** - Automatic spam filtering
- **CAPTCHA Ready** - Integration support
- **Transient Caching** - Efficient rate limit tracking

### ⚡ **Developer Features**
- **Clean Code** - Well-documented and organized
- **WordPress Standards** - Follows WP coding standards
- **AJAX-Powered** - Fast, responsive interactions
- **Database Storage** - Persistent conversation logs
- **REST API Ready** - Extensible API endpoints
- **Hooks & Filters** - Easy customization

---

## 📸 Screenshots

### Chat Widget Interface
![Chat Widget](https://via.placeholder.com/800x400?text=BankBot+Chat+Widget)

### Analytics Dashboard
![Analytics](https://via.placeholder.com/800x400?text=Analytics+Dashboard)

### Settings Panel
![Settings](https://via.placeholder.com/800x400?text=Settings+Panel)

### Mobile View
![Mobile](https://via.placeholder.com/400x600?text=Mobile+View)

---

## 💿 Installation

### Method 1: WordPress Admin Panel

1. Download the `bankbot.php` file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the downloaded file
4. Click **Install Now**
5. Click **Activate Plugin**

### Method 2: Manual Installation

1. Download and extract the plugin
2. Upload to `/wp-content/plugins/bankbot/` directory
3. Activate through the **Plugins** menu in WordPress

### Method 3: FTP Upload

1. Download the plugin file
2. Upload via FTP to `/wp-content/plugins/`
3. Activate in WordPress admin panel

---

## 🚀 Quick Start

### Initial Setup (5 Minutes)

1. **Activate Plugin**
   - Upon activation, BankBot automatically:
     - Creates database table `wp_bankbot_conversations`
     - Sets up default configuration
     - Enables demo mode for testing

2. **Configure Basic Settings**
   - Navigate to **BankBot** in WordPress admin
   - Set your bot name (default: "BankBot")
   - Choose language (English or Arabic)
   - Enable/disable the chatbot

3. **Test Demo Mode**
   - Visit your website frontend
   - Click the chat bubble (bottom-right by default)
   - Try asking: "Hello", "Account", "Loan", "Help"
   - Demo mode provides intelligent offline responses

4. **Connect AI API** (Optional)
   - Get free API key from [auth.pollinations.ai](https://auth.pollinations.ai)
   - Enter API key in **BankBot > API Settings**
   - Disable demo mode
   - Enable streaming for typewriter effect

5. **Customize Appearance**
   - Go to **BankBot > Colors** for theme customization
   - Adjust layout in **BankBot > Layout**
   - Upload custom icon in **BankBot > Icon**

---

## ⚙️ Configuration

### General Settings

#### **Enable Chatbot**
Toggle to show/hide the chatbot on your website.

#### **Demo Mode**
- **Enabled**: Uses offline smart responses (no API required)
- **Disabled**: Connects to AI API for advanced responses

#### **Bot Name**
Display name shown in chat header (e.g., "BankBot", "Banking Assistant").

#### **Language**
- **English (LTR)**: Left-to-right text direction
- **العربية (RTL)**: Right-to-left text direction

---

### API Settings

#### **API Key**
Optional Bearer token from [pollinations.ai](https://auth.pollinations.ai) for higher rate limits.

```
Get your free API key:
1. Visit https://auth.pollinations.ai
2. Sign up or log in
3. Copy your API key
4. Paste in BankBot settings
```

#### **AI Model Options**
- **OpenAI**: GPT-based model (recommended)
- **OpenAI Fast**: Faster responses, slightly less accurate
- **Mistral**: Alternative open-source model
- **Claude**: Anthropic's Claude model

#### **Enable Streaming**
Real-time typewriter effect for responses. Requires AI API (not available in demo mode).

---

### Messages Configuration

#### **Welcome Messages**
- **English**: "Hello! How can I help you today?"
- **Arabic**: "مرحبا! كيف يمكنني مساعدتك اليوم؟"

#### **Input Placeholders**
- **English**: "Type your message..."
- **Arabic**: "اكتب رسالتك..."

---

### Context Training

#### **General Context**
Provide background information about your bank or business.

**Example (English)**:
```
You are a helpful banking assistant for ABC Bank. 
We specialize in Islamic banking solutions.
Be professional, friendly, and informative.
```

**Example (Arabic)**:
```
أنت مساعد مصرفي مفيد لبنك ABC.
نحن متخصصون في الحلول المصرفية الإسلامية.
كن محترفًا وودودًا ومفيدًا.
```

#### **Products Context**
List your banking products and services.

**Example**:
```
Products & Services:
- Savings Account (0.5% APY, no minimum balance)
- Current Account (Free monthly maintenance)
- Islamic Home Finance (Murabaha-based)
- Personal Financing (Up to $50,000)
- Credit Cards (Cash back rewards)
```

#### **Services Context**
Customer service information.

**Example**:
```
Customer Services:
- 24/7 Online Banking
- Mobile App (iOS & Android)
- Branch Network (50+ locations)
- ATM Access (1000+ nationwide)
- Customer Support: 1-800-BANK-123
```

#### **Policies Context**
Important policies and guidelines.

**Example**:
```
Policies & Guidelines:
- Sharia-compliant banking
- Zero interest (Riba-free)
- Ethical investment only
- Customer privacy protection
- Transparent fee structure
```

---

## 🔌 API Integration

### Using Pollinations AI (Free)

BankBot uses [Pollinations AI](https://pollinations.ai) - a free, open API:

**Features:**
- ✅ No credit card required
- ✅ Multiple AI models
- ✅ Streaming support
- ✅ High rate limits with API key
- ✅ Open source

**Setup:**
1. Visit [auth.pollinations.ai](https://auth.pollinations.ai)
2. Create free account
3. Copy API key
4. Paste in **BankBot > API Settings**
5. Disable demo mode
6. Test your chatbot

### API Endpoints Used

```php
// Non-streaming endpoint
POST https://text.pollinations.ai/openai

// Streaming endpoint
POST https://text.pollinations.ai/openai (with stream: true)
```

### Request Format

```json
{
  "model": "openai",
  "messages": [
    {
      "role": "system",
      "content": "You are a helpful banking assistant."
    },
    {
      "role": "user",
      "content": "What accounts do you offer?"
    }
  ],
  "stream": true
}
```

---

## 🎨 Customization

### Color Scheme

BankBot provides **13 customizable colors**:

| Element | Default | Description |
|---------|---------|-------------|
| Header Background | `#0066cc` | Chat header color |
| Header Text | `#ffffff` | Header text color |
| User Bubble | `#0066cc` | User message background |
| User Text | `#ffffff` | User message text |
| Bot Bubble | `#f0f0f0` | Bot message background |
| Bot Text | `#333333` | Bot message text |
| Input Border | `#e0e0e0` | Input field border |
| Send Button | `#0066cc` | Send button color |
| Input Area BG | `#ffffff` | Input area background |
| Toggle BG | `#0066cc` | Chat toggle button |
| Messages BG | `#f5f5f5` | Chat messages area |

### Typography

#### **Font Families**
- System Default (recommended)
- Arial
- Helvetica
- Georgia
- Verdana
- Arabic Font (for RTL)

#### **Font Sizes**
- Message Font: 12-24px (default: 15px)
- Header Font: 14-28px (default: 18px)

#### **Font Weights**
- Normal (400)
- Medium (500)
- Semi-Bold (600)
- Bold (700)

#### **Line Height**
- Compact (1.4)
- Normal (1.6)
- Relaxed (1.8)

### Layout Options

#### **Position**
- Bottom Right (default)
- Bottom Left
- Top Right
- Top Left

#### **Dimensions**
- Button Size: 40-100px (default: 60px)
- Window Width: 300-600px (default: 380px)
- Window Height: 400-800px (default: 600px)
- Border Radius: 0-32px (default: 16px)
- Message Radius: 0-32px (default: 18px)
- Header Height: 50-100px (default: 70px)
- Input Height: 40-80px (default: 60px)

### Icon Customization

#### **Icon Types**
1. **Emoji** (default: 💬)
   - Any Unicode emoji
   - Examples: 💬 🤖 💡 📱 👋

2. **Text**
   - Custom text (e.g., "Chat", "Help")
   - Font size auto-adjusts

3. **Custom Image**
   - Upload your logo/icon
   - Recommended: 512x512px PNG
   - Auto-resized to fit button

#### **Header Icon**
Toggle to show/hide icon in chat header.

---

## 📊 Analytics Dashboard

### Overview Cards

The dashboard displays 3 key metrics:

1. **Total Conversations**: All-time message count
2. **Active Days**: Days with at least one conversation
3. **Avg Daily**: Average conversations per active day

### Interactive Chart

Visual trend line showing daily conversation volume:
- **Time Range**: Last 30 days by default
- **Filtering**: Custom date range selection
- **Sorting**: Ascending or descending order
- **Hover Info**: Exact count per day

### Most Common Queries

Top 5 most frequently asked questions with count.

**Use this to:**
- Identify popular topics
- Improve context training
- Create FAQ sections
- Optimize responses

### All Conversations Table

Paginated table with **20 conversations per page**:

**Columns:**
- Date & Time
- User Message (full text)
- Bot Response (full text)
- IP Address
- Hostname (detected via WebRTC)

**Filters:**
- Search user message
- Search bot response
- Search IP address
- Search hostname
- Sort options (newest, oldest, A-Z, Z-A, today, this week, this month, this year)

**Pagination:**
- Previous/Next buttons
- Page number input
- Jump to specific page

### Data Management

#### **Export to CSV**
Download all conversations as CSV file:
- One-click export
- All fields included
- Filename: `bankbot_conversations_YYYY-MM-DD.csv`

#### **Import from CSV**
Upload previously exported CSV:
- Bulk import conversations
- Preserves all data
- Shows import count

#### **Delete Conversations**
Two deletion options:
1. **Delete All**: Removes all conversations (requires confirmation)
2. **Delete Date Range**: Remove conversations between specific dates

#### **IP Blacklist**
Block specific IP addresses:
- Enter one IP per line
- Blocked IPs cannot use chatbot
- Useful for spam prevention

---

## 🛡️ Anti-Spam Protection

### Rate Limiting

Prevent spam and abuse with configurable rate limits:

**Settings:**
- **Messages Limit**: Max messages per time window (default: 5)
- **Time Window**: Duration in seconds (default: 60)

**Example:**
```
5 messages per 60 seconds = 1 message every 12 seconds max
```

### IP Blacklist

Manually block problematic IPs:

1. Go to **BankBot > Analytics**
2. Scroll to **Data Management**
3. Find **IP Blacklist** section
4. Enter IP addresses (one per line)
5. Click **Save Blacklist**

**Example:**
```
192.168.1.100
10.0.0.50
203.45.67.89
```

### How It Works

1. **First Message**: User sends message, counter starts
2. **Subsequent Messages**: Counter increments
3. **Limit Reached**: User blocked for time window duration
4. **Time Expires**: Counter resets, user can message again

**Backend Technology:**
- WordPress Transients API
- Per-IP tracking
- Automatic expiration
- No database overhead

---

## 🌍 Multi-Language Support

### Supported Languages

#### **English (LTR)**
- Left-to-right text direction
- Western character sets
- Default language

#### **Arabic (RTL)**
- Right-to-left text direction
- Arabic Unicode support
- Proper text alignment
- RTL-optimized UI

### Configuration

1. Go to **BankBot > General Settings**
2. Select language from dropdown
3. Set welcome message for selected language
4. Set input placeholder for selected language
5. Save settings

### Adding More Languages

BankBot is extensible for additional languages:

```php
// Add to your theme's functions.php
add_filter('bankbot_languages', function($languages) {
    $languages['fr'] = 'Français';
    $languages['es'] = 'Español';
    return $languages;
});
```

---

## 📝 Shortcodes

Currently, BankBot automatically displays on all frontend pages. Manual shortcode implementation coming soon.

### Planned Shortcodes (v3.3.0)

```php
// Display chatbot inline
[bankbot]

// Display with custom position
[bankbot position="inline"]

// Display analytics (admin only)
[bankbot_analytics]
```

---

## ❓ FAQs

### **Q: Is BankBot free?**
**A:** Yes! BankBot is 100% free and open source. The AI API (Pollinations) is also free.

### **Q: Do I need an API key?**
**A:** No, demo mode works offline. For advanced AI responses, a free API key is recommended but optional.

### **Q: Does it support other languages?**
**A:** Currently English and Arabic. More languages can be added via custom development.

### **Q: Can I customize the appearance?**
**A:** Yes! 13 colors, typography, layout, icons, and more are fully customizable.

### **Q: Is it mobile-friendly?**
**A:** Yes, BankBot is fully responsive and optimized for mobile devices.

### **Q: Does it store conversations?**
**A:** Yes, all conversations are stored in the WordPress database for analytics.

### **Q: Can I export conversation data?**
**A:** Yes, export to CSV from the Analytics dashboard.

### **Q: How do I prevent spam?**
**A:** Enable anti-spam protection in **BankBot > Anti-Spam** with configurable rate limits.

### **Q: Does it work with multisite?**
**A:** Currently single-site only. Multisite compatibility planned for future releases.

### **Q: Can I integrate with my CRM?**
**A:** Custom integration is possible via WordPress hooks and filters.

### **Q: Is GDPR compliant?**
**A:** Stores minimal data (IP, messages). Add your privacy policy link to bot context.

### **Q: Does it work offline?**
**A:** Demo mode works offline. API mode requires internet connection.

---

## 🔧 Troubleshooting

### Chat Widget Not Showing

**Solution:**
1. Check if plugin is activated
2. Verify "Enable Chatbot" is ON in settings
3. Clear browser cache
4. Check for JavaScript errors in browser console
5. Disable other chatbot plugins

### API Responses Not Working

**Solution:**
1. Verify API key is correct
2. Check internet connection
3. Enable demo mode temporarily
4. Review PHP error logs
5. Test with different AI model

### Streaming Not Working

**Solution:**
1. Verify streaming is enabled in settings
2. Confirm API key is valid
3. Check server supports Server-Sent Events (SSE)
4. Disable demo mode
5. Test with non-streaming mode first

### Arabic Text Not Displaying Properly

**Solution:**
1. Ensure WordPress language is set correctly
2. Use "Arabic Font" in typography settings
3. Verify language is set to "ar" in BankBot settings
4. Check database charset is utf8mb4
5. Clear all caches

### Analytics Not Loading

**Solution:**
1. Check Chart.js is loading (view page source)
2. Verify AJAX requests in browser network tab
3. Check WordPress user has `manage_options` capability
4. Clear transient cache
5. Deactivate conflicting plugins

### Rate Limiting Not Working

**Solution:**
1. Verify anti-spam is enabled
2. Check transients are supported by your host
3. Test with different IP address
4. Review rate limit settings (messages/time)
5. Clear all WordPress transients

---

## 🛣️ Roadmap

### Version 3.3.0 (Planned - Q2 2025)
- [ ] Inline shortcode support
- [ ] WhatsApp integration
- [ ] Email notifications for conversations
- [ ] Conversation tagging and categories
- [ ] Advanced analytics (sentiment analysis)
- [ ] Multi-agent conversations

### Version 3.4.0 (Future)
- [ ] Voice input support
- [ ] File attachment handling
- [ ] Live agent handoff
- [ ] CRM integrations (Salesforce, HubSpot)
- [ ] Zapier webhook support
- [ ] Custom bot personality presets

### Version 4.0.0 (Future)
- [ ] Multisite network support
- [ ] REST API for external integrations
- [ ] Chatbot marketplace (templates)
- [ ] A/B testing for responses
- [ ] Machine learning optimization
- [ ] Advanced GDPR compliance tools

---

## 🤝 Contributing

Contributions are welcome! Here's how you can help:

### Ways to Contribute

1. **Report Bugs**: Open an issue with detailed information
2. **Suggest Features**: Share your ideas for improvements
3. **Submit Pull Requests**: Fork, code, and submit PRs
4. **Improve Documentation**: Help make docs clearer
5. **Translate**: Add support for more languages
6. **Test**: Try new features and report findings

### Development Setup

```bash
# Clone the repository
git clone https://github.com/houssemdub/bankbot.git

# Install in WordPress plugins directory
cp bankbot.php /path/to/wordpress/wp-content/plugins/

# Activate via WordPress admin
# Start developing!
```

### Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/)
- Use proper escaping and sanitization
- Comment complex logic
- Test thoroughly before submitting

### Pull Request Process

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request with detailed description

---

## 👨‍💻 Credits

**Developed by:** Mohamed Houssem Eddine SAIGHI  
**AI Assistant:** Claude Sonnet 4.5  
**Version:** 3.2.0  
**License:** GPL-2.0-or-later  

### Technologies Used

- **WordPress** - Core CMS platform
- **jQuery** - JavaScript library
- **Pollinations AI** - Free AI API
- **Chart.js** - Analytics visualization
- **PHP 7.4+** - Server-side logic
- **MySQL** - Database storage
- **WebRTC** - Hostname detection
- **AJAX** - Asynchronous operations

### Special Thanks

- Pollinations AI for free API access
- WordPress community for standards and tools
- Claude Sonnet 4.5 for development assistance
- All contributors and testers

---

## 📄 License

This plugin is licensed under the **GPL v2 or later**.

```
BankBot - AI Islamic Banking Assistant
Copyright (C) 2024 Mohamed Houssem Eddine SAIGHI

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
```

---

## 🔗 Links

- **GitHub Repository**: [https://github.com/houssemdub/bankbot](https://github.com/houssemdub/bankbot)
- **Issues & Bug Reports**: [https://github.com/houssemdub/bankbot/issues](https://github.com/houssemdub/bankbot/issues)
- **API Documentation**: [https://pollinations.ai](https://pollinations.ai)
- **WordPress Plugin Page**: Coming Soon
- **Live Demo**: Coming Soon

---

## ⭐ Show Your Support

If BankBot helps your business, please:
- ⭐ **Star the repository** on GitHub
- 🐛 **Report bugs** to help improve the plugin
- 💡 **Suggest features** you'd like to see
- 📢 **Share** with others who might benefit
- ☕ **Buy me a coffee** (optional donation)

---

## 📞 Support

For support, questions, or feature requests:

- 📧 **Email**: contact@yourdomain.com
- 🐛 **GitHub Issues**: [Open an Issue](https://github.com/houssemdub/bankbot/issues)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/houssemdub/bankbot/discussions)
- 🌐 **Website**: Coming Soon

---

## 🏆 Awards & Recognition

- ⭐ Featured on WordPress.org (Coming Soon)
- 🏅 5-Star Rating (Coming Soon)
- 🎯 1000+ Active Installations (Coming Soon)

---

<div align="center">

**Made with ❤️ by [Mohamed Houssem Eddine SAIGHI](https://github.com/houssemdub)**

*Powered by Claude Sonnet 4.5 & Pollinations AI*

**Enhancing customer experience through intelligent conversations**

</div>

---

## 📚 Additional Resources

### Tutorials
- [Getting Started with BankBot](https://github.com/houssemdub/bankbot/wiki/Getting-Started)
- [Advanced Customization Guide](https://github.com/houssemdub/bankbot/wiki/Customization)
- [API Integration Tutorial](https://github.com/houssemdub/bankbot/wiki/API-Setup)
- [Analytics Deep Dive](https://github.com/houssemdub/bankbot/wiki/Analytics)

### Video Guides
- Introduction to BankBot (Coming Soon)
- Complete Setup Tutorial (Coming Soon)
- Customization Walkthrough (Coming Soon)

### Community
- Join our Discord (Coming Soon)
- Follow on Twitter (Coming Soon)
- Subscribe to Newsletter (Coming Soon)

---

**Last Updated:** October 2025  
**Documentation Version:** 3.2.0
