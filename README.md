````md
# 💬 BankBot - AI Islamic Banking Assistant

![Version: v3.2.0](https://img.shields.io/badge/version-v3.2.0-green)
![License: GPL v2 or later](https://img.shields.io/badge/license-GPL_v2_or_later-blue)

A powerful WordPress plugin that provides an intelligent AI chatbot specifically designed for Islamic banking websites. Built with multi-language support, streaming responses, and comprehensive analytics.

## ✨ Overview

* 🤖 **AI-Powered**: Integrates with OpenAI and Pollinations.ai for intelligent responses.
* 🌍 **Multi-Language**: Full support for English and Arabic (RTL).
* 📊 **Analytics Dashboard**: Track conversations, users, and chatbot performance.
* 🛡️ **Anti-Spam**: Built-in rate limiting and IP blacklist protection.

## 📋 Table of Contents

* [Features](#-features)
* [Installation](#-installation)
* [Configuration](#-configuration)
* [Usage](#-usage)
* [FAQ](#-faq)
* [License & Credits](#-license--credits)

## ✨ Features

### Core Functionality
* ✅ AI-powered chatbot with OpenAI/Pollinations.ai integration
* ✅ Real-time streaming responses for natural conversations
* ✅ Multi-language support (English & Arabic with RTL)
* ✅ Customizable AI contexts for products, services, and policies
* ✅ Session-based conversation history
* ✅ Demo mode for testing without API keys

### Analytics & Reporting
* 📊 Professional analytics dashboard with GitHub-style UI
* 📊 Conversation tracking with IP addresses
* 📊 Chart.js integration for data visualization
* 📊 Advanced filtering (date range, search, sort)
* 📊 Export conversations to CSV
* 📊 Import conversations from CSV

### Security & Protection
* 🛡️ Configurable anti-spam protection
* 🛡️ Rate limiting (customizable messages per time window)
* 🛡️ IP blacklist management
* 🛡️ AJAX nonce verification
* 🛡️ Sanitized inputs and SQL injection protection

### Customization
* 🎨 Full color customization (16+ color options)
* 🎨 Typography settings (fonts, sizes, weights)
* 🎨 Layout options (size, position, borders, shadows)
* 🎨 Custom icons (emoji, text, or image upload)
* 🎨 Animation settings (speed and shadow intensity)
* 🎨 Responsive design for all devices

### Data Management
* 💾 CSV export/import functionality
* 💾 Bulk delete conversations
* 💾 Date range deletion
* 💾 Automatic database table creation
* 💾 Table structure verification and repair

## 📦 Installation

### Method 1: WordPress Admin
1.  Download the plugin zip file from GitHub
2.  Go to WordPress Admin → Plugins → Add New
3.  Click "Upload Plugin" and choose the zip file
4.  Click "Install Now" and then "Activate"

### Method 2: FTP Upload
1.  Extract the plugin zip file
2.  Upload the `bankbot` folder to `/wp-content/plugins/`
3.  Go to WordPress Admin → Plugins
4.  Find "BankBot" and click "Activate"

### Method 3: WP-CLI
```bash
wp plugin install bankbot.zip --activate
````

### Database Setup

The plugin automatically creates the required database table upon activation. If needed, the table structure is verified and repaired on every admin page load.

#### Manual Table Creation (if needed)

```sql
CREATE TABLE wp_bankbot_conversations (
  id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id VARCHAR(255) NOT NULL,
  user_id BIGINT(20) UNSIGNED,
  user_message TEXT NOT NULL,
  bot_response TEXT NOT NULL,
  ip_address VARCHAR(45),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## ⚙️ Configuration

### 1\. General Settings

Navigate to **WordPress Admin → BankBot → Settings**

**Enable/Disable Plugin**

  * Toggle the "Enable BankBot" switch to activate or deactivate the chatbot

**Bot Name**

  * Set a custom name for your chatbot (default: "BankBot")

**Language Selection**

  * Choose between English (en) or Arabic (ar)
  * Arabic automatically enables RTL (right-to-left) layout

### 2\. API Configuration

**API Provider**
| Provider | API Key Required | Cost |
| :--- | :--- | :--- |
| Pollinations.ai | Optional | Free (rate limited) |
| OpenAI | Yes | Pay per use |

**Getting API Keys**

  * **Pollinations.ai:** Visit [auth.pollinations.ai](https://auth.pollinations.ai) (optional, for higher limits)
  * **OpenAI:** Visit [platform.openai.com/api-keys](https://platform.openai.com/api-keys)

### 3\. AI Models

  * **OpenAI:** GPT-3.5 Turbo (default, balanced)
  * **OpenAI Fast:** Faster responses, lower quality
  * **Mistral:** Open-source alternative
  * **Claude:** Anthropic's model

### 4\. Contexts & Messages

```
English Context: "You are a helpful Islamic banking assistant..."
Arabic Context: "أنت مساعد مصرفي إسلامي مفيد..."

Products Context: "Our bank offers Murabaha, Ijara, Musharaka..."
Services Context: "We provide account opening, transfers..."
Policies Context: "All transactions are Shariah-compliant..."
```

### 5\. Customization

**Colors**

  * Header Background & Text
  * User Bubble & Text
  * Bot Bubble & Text
  * Input Border & Background
  * Send Button & Toggle

**Layout**

  * Widget Position: Bottom-Right, Bottom-Left, Top-Right, Top-Left
  * Button Size: 40px - 100px
  * Chat Window: Width & Height
  * Border Radius: 0px - 30px

**Typography**

  * Font Family: System, Arial, Helvetica, Arabic fonts
  * Font Size: 12px - 20px
  * Header Size & Weight

### 6\. Anti-Spam Settings

```
Enable Anti-Spam: ON/OFF
Rate Limit Messages: 5 (default)
Rate Limit Time Window: 60 seconds (default)

Examples:
- 5 messages per 60 seconds = 5 msg/minute
- 10 messages per 120 seconds = 10 msg/2 minutes
- 20 messages per 3600 seconds = 20 msg/hour
```

### 7\. IP Blacklist

Go to **Analytics → Data Management → IP Blacklist**

```
192.168.1.100
10.0.0.50
203.0.113.42
```

Add one IP address per line. Blocked IPs cannot use the chatbot.

## 🚀 Usage

### Frontend Widget

Once activated and configured, the chatbot widget automatically appears on all pages of your website.

**User Interaction**

1.  Click the floating chat button (bottom-right by default)
2.  Type a message in the input field
3.  Press Enter or click the Send button
4.  Receive AI-powered responses in real-time
5.  Continue the conversation naturally

### Analytics Dashboard

Access: **WordPress Admin → BankBot → Analytics**

**Key Metrics**

  * **Total Conversations:** Number of all conversation exchanges
  * **Unique Users:** Count of distinct users/IPs
  * **Avg Message Length:** Average character count of user messages

**Conversations Chart**
Line chart showing conversations over time (last 30 days)

**Filtering Options**

  * Date Range: Select start and end dates
  * Search: Filter by user message, bot response, or IP address
  * Sort: Newest, Oldest, A-Z, Z-A, This Year, This Month, This Week, Today

### Data Management

**Export Conversations**

1.  Go to Analytics Dashboard
2.  Click "📥 Export to CSV"
3.  CSV file downloads automatically

**Import Conversations**

1.  Prepare CSV file with proper format
2.  Click "📤 Import from CSV"
3.  Select your CSV file
4.  Conversations are imported automatically

**Delete Conversations**

  * **Delete All:**

    ```
    Click "🗑️ Delete All" button → Confirm → All conversations deleted
    ```

  * **Delete by Date Range:**

    ```
    Select start date → Select end date → Click "🗑️ Delete Date Range" → Confirm
    ```

### Shortcodes

Currently, the plugin automatically displays on all pages. Manual shortcode placement is not required but may be added in future versions.

## ❓ FAQ

### General Questions

#### Q: Do I need an API key to use BankBot?

A: No, you can use the demo mode without any API key. However, for production use, an API key is recommended for better performance and no rate limits.

#### Q: Which AI models are supported?

A: BankBot supports OpenAI (GPT-3.5, GPT-4), OpenAI Fast, Mistral, and Claude models via Pollinations.ai API.

#### Q: Can I customize the chatbot appearance?

A: Yes\! You can customize 16+ colors, fonts, sizes, positions, borders, shadows, animations, and icons.

#### Q: Does it support RTL languages?

A: Yes, Arabic language automatically enables RTL (right-to-left) layout support.

### Technical Questions

#### Q: Where are conversations stored?

A: All conversations are stored in your WordPress database in the `wp_bankbot_conversations` table.

#### Q: Can I export conversation data?

A: Yes, you can export all conversations to CSV format from the Analytics Dashboard.

#### Q: How does the anti-spam protection work?

A: The plugin uses rate limiting (configurable messages per time window) and IP blacklist to prevent abuse.

#### Q: What happens if the database table is missing?

A: The plugin automatically checks and creates the table structure on every admin page load.

### Troubleshooting

#### Q: The chatbot is not appearing on my site

A: Check that the plugin is activated and "Enable BankBot" is turned ON in settings.

#### Q: API responses are slow or failing

A: Verify your API key is correct and you have sufficient credits/quota with your AI provider.

#### Q: How do I reset all settings?

A: Deactivate and reactivate the plugin, or delete the `bankbot_settings` option from the database.

#### Q: Can I use this on multiple sites?

A: Yes, the plugin can be installed on multiple WordPress sites independently.

### Security Questions

#### Q: Is my data secure?

A: All data is stored in your WordPress database. API communications use HTTPS. The plugin uses nonce verification and input sanitization.

#### Q: How do I block specific IP addresses?

A: Go to Analytics → Data Management → IP Blacklist, and add IP addresses (one per line).

#### Q: Can users see other users' conversations?

A: No, each user has their own isolated conversation session. Only admins can view all conversations in the Analytics Dashboard.

## 📜 License & Credits

  * **License:** Licensed under GPL v2 or later
  * **Authors:** Made with ❤️ by Mohamed Houssem Eddine SAIGHI & Claude Sonnet 4.5
  * **Source:** [GitHub](https://github.com/houssemdub/bankbot)

<!-- end list -->

```
```
