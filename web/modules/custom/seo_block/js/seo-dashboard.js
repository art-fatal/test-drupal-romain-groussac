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

      // Search functionality
      $('#content-search', context).once('seo-dashboard').on('input', function () {
        self.filterContent();
      });

      // Status filter
      $('#status-filter', context).once('seo-dashboard').on('change', function () {
        self.filterContent();
      });

      // Items per page selector
      $('#items-per-page', context).once('seo-dashboard').on('change', function () {
        self.changeItemsPerPage($(this).val());
      });

      // Initialize content filtering
      this.filterContent();
    },

    clearCache: function () {
      const button = $('#clear-cache-btn');
      const originalText = button.html();
      const self = this;

      button.addClass('loading');

      $.ajax({
        url: '/admin/content/seo-dashboard/clear-cache',
        method: 'POST',
        dataType: 'json',
        success: function (response) {
          if (response.success) {
            window.location.reload();
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

    changeItemsPerPage: function (itemsPerPage) {
      // Get current URL parameters
      const urlParams = new URLSearchParams(window.location.search);
      
      // Update items_per_page parameter
      urlParams.set('items_per_page', itemsPerPage);
      
      // Reset to first page when changing items per page
      urlParams.set('page', '0');
      
      // Build new URL
      const newUrl = window.location.pathname + '?' + urlParams.toString();
      
      // Redirect to new URL
      window.location.href = newUrl;
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



})(jQuery, Drupal);
