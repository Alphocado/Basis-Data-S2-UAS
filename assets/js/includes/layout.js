// Optional: Add any global JavaScript functionality
document.addEventListener("DOMContentLoaded", function () {
  // Example: Confirm logout
  const logoutLinks = document.querySelectorAll('a[href="../logout.php"]');
  logoutLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      if (!confirm("Apakah Anda yakin ingin logout?")) {
        e.preventDefault();
      }
    });
  });
});
