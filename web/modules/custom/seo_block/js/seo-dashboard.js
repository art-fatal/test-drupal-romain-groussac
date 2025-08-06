/**
 * SEO Dashboard JavaScript
 */

(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.seoDashboard = {
    attach: function (context, settings) {
      // Initialize dashboard functionality
      this.initDashboard(context);
    },

    initDashboard: function (context) {
      const self = this;
      
      // Cache clear button
      $('#clear-cache-btn', context).once('seo-dashboard').on('click', function (e) {
        e.preventDefault();
        self.clearCache();
      });

      // SEO check button
      $('#seo-check-btn', context).once('seo-dashboard').on('click', function (e) {
        e.preventDefault();
        self.runSeoCheck();
      });

      // Search functionality
      $('#content-search', context).once('seo-dashboard').on('input', function () {
        self.filterContent();
      });

      // Status filter
      $('#status-filter', context).once('seo-dashboard').on('change', function () {
        self.filterContent();
      });

      // Modal close
      $('#close-modal', context).once('seo-dashboard').on('click', function () {
        $('#seo-results-modal').hide();
      });

      // Close modal on outside click
      $('#seo-results-modal', context).once('seo-dashboard').on('click', function (e) {
        if (e.target === this) {
          $(this).hide();
        }
      });

      // Initialize content filtering
      this.filterContent();
    },

    clearCache: function () {
      const button = $('#clear-cache-btn');
      const originalText = button.html();
      const self = this;
      
      // Show loading state
      button.addClass('loading').html('<i class="fas fa-spinner fa-spin"></i> Purge en cours...');
      
      $.ajax({
        url: '/admin/content/seo-dashboard/clear-cache',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            self.showNotification('Cache vidé avec succès !', 'success');
            // Update last cache clear time
            $('.stat-card:last-child .stat-card__value').text(response.timestamp);
          } else {
            self.showNotification('Erreur lors de la purge du cache: ' + response.message, 'error');
          }
        },
        error: function (xhr, status, error) {
          self.showNotification('Erreur lors de la purge du cache: ' + error, 'error');
        },
        complete: function () {
          // Restore button state
          button.removeClass('loading').html(originalText);
        }
      });
    },

    runSeoCheck: function () {
      const button = $('#seo-check-btn');
      const originalText = button.html();
      const self = this;
      
      // Show loading state
      button.addClass('loading').html('<i class="fas fa-spinner fa-spin"></i> Vérification...');
      
      $.ajax({
        url: '/admin/content/seo-dashboard/seo-check',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            self.showNotification('Vérification SEO terminée !', 'success');
            self.showSeoResults(response.results);
          } else {
            self.showNotification('Erreur lors de la vérification SEO: ' + response.message, 'error');
          }
        },
        error: function (xhr, status, error) {
          self.showNotification('Erreur lors de la vérification SEO: ' + error, 'error');
        },
        complete: function () {
          // Restore button state
          button.removeClass('loading').html(originalText);
        }
      });
    },

    showSeoResults: function (results) {
      let html = '<div class="seo-results">';
      
      if (results.length === 0) {
        html += '<p>Aucun contenu trouvé pour la vérification SEO.</p>';
      } else {
        html += '<table class="seo-results-table">';
        html += '<thead><tr><th>Contenu</th><th>Score SEO</th><th>Statut</th></tr></thead>';
        html += '<tbody>';
        
        results.forEach(function (item) {
          const statusClass = item.score >= 70 ? 'good' : (item.score >= 40 ? 'medium' : 'poor');
          html += '<tr>';
          html += '<td>' + Drupal.checkPlain(item.title) + '</td>';
          html += '<td><span class="seo-score-badge seo-score-badge--' + statusClass + '">' + item.score + '/100</span></td>';
          html += '<td><span class="status-badge status-badge--' + item.status.toLowerCase() + '">' + Drupal.checkPlain(item.status) + '</span></td>';
          html += '</tr>';
        });
        
        html += '</tbody></table>';
      }
      
      html += '</div>';
      
      $('#seo-results-content').html(html);
      $('#seo-results-modal').show();
    },

    filterContent: function () {
      const searchTerm = $('#content-search').val().toLowerCase();
      const statusFilter = $('#status-filter').val();
      
      $('.content-row').each(function () {
        const row = $(this);
        const title = row.data('title');
        const status = row.data('status');
        
        let show = true;
        
        // Search filter
        if (searchTerm && !title.includes(searchTerm)) {
          show = false;
        }
        
        // Status filter
        if (statusFilter && status !== statusFilter) {
          show = false;
        }
        
        if (show) {
          row.show();
        } else {
          row.hide();
        }
      });
      
      // Update row count
      const visibleRows = $('.content-row:visible').length;
      const totalRows = $('.content-row').length;
      
      if (searchTerm || statusFilter) {
        $('.content-header h2').text('Liste des contenus (' + visibleRows + '/' + totalRows + ')');
      } else {
        $('.content-header h2').text('Liste des contenus');
      }
    },

    showNotification: function (message, type) {
      // Ensure notifications container exists
      if ($('#notifications').length === 0) {
        $('body').append('<div id="notifications" class="notifications"></div>');
      }
      
      const notification = $('<div class="notification notification--' + type + '">' + Drupal.checkPlain(message) + '</div>');
      
      $('#notifications').append(notification);
      
      // Auto remove after 5 seconds
      setTimeout(function () {
        notification.fadeOut(300, function () {
          $(this).remove();
        });
      }, 5000);
    }
  };

  // Add fallback for Drupal.checkPlain if not available
  if (typeof Drupal.checkPlain === 'undefined') {
    Drupal.checkPlain = function (str) {
      if (typeof str !== 'string') {
        return str;
      }
      return str
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    };
  }

  // Add some additional styles for SEO results
  const additionalStyles = `
    <style>
      .seo-results-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
      }
      
      .seo-results-table th,
      .seo-results-table td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #e1e5e9;
      }
      
      .seo-results-table th {
        background: #f8f9fa;
        font-weight: 600;
      }
      
      .seo-score-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
      }
      
      .seo-score-badge--good {
        background: #d4edda;
        color: #155724;
      }
      
      .seo-score-badge--medium {
        background: #fff3cd;
        color: #856404;
      }
      
      .seo-score-badge--poor {
        background: #f8d7da;
        color: #721c24;
      }
    </style>
  `;
  
  $('head').append(additionalStyles);

})(jQuery, Drupal); 