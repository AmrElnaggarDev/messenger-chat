# Messenger Chat 💬

[![Laravel](https://img.shields.io/badge/Laravel-9.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=for-the-badge&logo=vue.js)](https://vuejs.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg?style=for-the-badge)](LICENSE)

A high-performance, real-time messaging application built with **Laravel 9** and **Vue 3**. This project demonstrates a modern approach to real-time communication, featuring a seamless SPA-like transition, secure API authentication, and robust message delivery.

---

## ✨ Key Features

- **🚀 Real-time Communication**: Instant messaging powered by Laravel Echo and Pusher.
- **📄 Attachment Support**: Send images and files seamlessly within conversations.
- **👥 Conversation Management**: Support for private (peer-to-peer) and group chat architectures.
- **🔔 Unread Notifications**: Real-time unread message counters and "Mark as Read" functionality.
- **✍️ Typing Indicators**: Live "User is typing..." indicators for enhanced user engagement.
- **🟢 Online status**: Real-time presence indicators to see who is currently active.
- **🔍 Instant Search & Filter**: Find conversations and messages instantly with real-time filtering.
- **🕒 Message History**: Efficient loading of message history with scrollable chat windows.
- **🗑️ Message Security**: Option to delete messages (soft-delete for user view).

---

## 🛠️ Tech Stack

### Backend
- **Framework**: [Laravel 9](https://laravel.com)
- **Authentication**: [Laravel Sanctum](https://laravel.com/docs/sanctum)
- **Starter Kit**: [Laravel Breeze](https://laravel.com/docs/breeze)
- **Database**: MySQL / PostgreSQL
- **Broadcasting**: [Pusher](https://pusher.com)

### Frontend
- **Framework**: [Vue.js 3](https://vuejs.org) (Composition API)
- **Styling**: [Tailwind CSS](https://tailwindcss.com)
- **Real-time Client**: [Laravel Echo](https://laravel.com/docs/broadcasting)
- **Build Tool**: [Laravel Mix](https://laravel-mix.com)

---

## 🚀 Getting Started

### Prerequisites
- PHP 8.0+
- Composer
- Node.js & NPM
- MySQL

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/AmrElnaggarDev/messenger-chat.git
   cd messenger-chat
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Frontend dependencies**
   ```bash
   npm install
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   ```
   *Edit `.env` and configure your database and Pusher credentials.*

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations**
   ```bash
   php artisan migrate
   ```

7. **Compile Assets**
   ```bash
   npm run dev
   ```

8. **Start the Server**
   ```bash
   php artisan serve
   ```

---

## 📖 Usage

- **Starting a Chat**: Use the sidebar to search for users and click to initiate a conversation.
- **Sending Files**: Click the attachment icon to share images or documents.
- **Read Receipts**: Messages will automatically mark as read when the conversation is active.

---

## 📜 License

Distributed under the MIT License. See `LICENSE` for more information.

---

## 👨‍💻 Author

**Amr Elnaggar**
- GitHub: [@AmrElnaggarDev](https://github.com/AmrElnaggarDev)
- Projects: [Messenger Chat](https://github.com/AmrElnaggarDev/messenger-chat)
