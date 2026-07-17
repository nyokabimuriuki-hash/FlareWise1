/* Browser reminders for scheduled medication and skincare routines.
   Notifications are delivered while FlareWise is open in a browser tab. */
(function () {
  const reminders = Array.isArray(window.flarewiseReminders) ? window.flarewiseReminders : [];
  const seenKey = 'flarewise-reminders-shown-' + new Date().toISOString().slice(0, 10);

  function shown() {
    try { return JSON.parse(sessionStorage.getItem(seenKey) || '[]'); } catch (_) { return []; }
  }
  function remember(id) {
    const ids = shown();
    if (!ids.includes(id)) { ids.push(id); sessionStorage.setItem(seenKey, JSON.stringify(ids)); }
  }
  function showInApp(message) {
    let box = document.getElementById('reminder-alert');
    if (!box) {
      box = document.createElement('div'); box.id = 'reminder-alert'; box.className = 'reminder-alert';
      box.setAttribute('role', 'alert'); document.body.appendChild(box);
    }
    box.textContent = message; box.hidden = false;
    clearTimeout(box.hideTimer); box.hideTimer = setTimeout(() => { box.hidden = true; }, 10000);
  }
  function notify(reminder) {
    const message = `Time for ${reminder.name}${reminder.dosage ? ': ' + reminder.dosage : ''}.`;
    showInApp(message);
    if ('Notification' in window && Notification.permission === 'granted') {
      new Notification('FlareWise reminder', { body: message, icon: '../assets/icons/icon-192.png' });
    }
  }
  function check() {
    const now = new Date(); const time = now.toTimeString().slice(0, 5);
    reminders.forEach(r => {
      const id = String(r.id) + '-' + time;
      if (r.time && r.time.slice(0, 5) === time && !shown().includes(id)) { remember(id); notify(r); }
    });
  }
  window.enableFlarewiseNotifications = function () {
    if (!('Notification' in window)) { showInApp('This browser does not support notifications. In-app alerts will still appear.'); return; }
    Notification.requestPermission().then(permission => {
      showInApp(permission === 'granted' ? 'Notifications are enabled for this browser.' : 'Notifications were not enabled. You will still see in-app alerts.');
    });
  };
  check(); setInterval(check, 30000);
})();
