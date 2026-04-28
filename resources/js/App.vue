<template>
  <router-view></router-view>
</template>

<script setup>
</script>

<style>
:root {
  --primary-color: #6366f1;
  --primary-hover: #4f46e5;
  --bg-color: #f8fafc;
  --card-bg: #ffffff;
  --text-main: #1e293b;
  --text-muted: #64748b;
  --border-color: #e2e8f0;
  --success: #10b981;
  --danger: #ef4444;
}

body {
  margin: 0;
  font-family: 'Inter', sans-serif;
  background-color: var(--bg-color);
  color: var(--text-main);
}
</style>
