// Firebase configuration
// TODO: Replace the values below with your Firebase project configuration.
// Get these from the Firebase Console -> Project settings.
var firebaseConfig = {
  apiKey: "AIzaSyB3kiW25AKuXEyQcCPAzJjydP0FTYfjZ5M",
  authDomain: "flarewise-4722c.firebaseapp.com",
  projectId: "flarewise-4722c",
  storageBucket: "flarewise-4722c.firebasestorage.app",
  messagingSenderId: "839357945205",
  appId: "1:839357945205:web:be37b8b278e0dda8802f3c",
  measurementId: "G-R541WP8CMV"
};

// Initialize Firebase (uses compat API loaded via CDN in pages)
if (typeof firebase !== 'undefined' && firebase.apps && !firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
}

// Helper: expose config for debugging if needed
window.__FIREBASE_CONFIG = firebaseConfig;
