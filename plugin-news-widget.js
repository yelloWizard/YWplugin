(async () => {
  const container = document.getElementById('plugin-news-widget');
  if (!container) return;

  try {
    const response = await fetch('https://yourwebsite.com/wp-json/wp/v2/posts?categories=24&per_page=3');
    const posts = await response.json();

    container.innerHTML = posts.map(post => `
      <div class="plugin-news-item" style="margin-bottom: 1.5em;">
        <h4 style="margin-bottom: 0.3em;">${post.title.rendered}</h4>
        <div>${post.excerpt.rendered}</div>
        <a href="${post.link}" target="_blank" style="color: #0073aa;">Διαβάστε περισσότερα</a>
      </div>
    `).join('');
  } catch (error) {
    container.innerHTML = '<p>Δεν ήταν δυνατή η φόρτωση των ανακοινώσεων.</p>';
    console.error('Plugin News Widget Error:', error);
  }
})();
