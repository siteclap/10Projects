/**
 * 10Projects Admin Scripts
 *
 * Handles tabbed meta boxes, media uploads, gallery fields, repeater fields.
 *
 * @package TenProjects
 * @since 1.0.0
 */

(function ($) {
  'use strict';

  /**
   * Tabbed Meta Boxes
   */
  function initMetaTabs() {
    $(document).on('click', '.tp-meta-tab', function (e) {
      e.preventDefault();
      const $tab = $(this);
      const target = $tab.data('tab');
      const $container = $tab.closest('.tp-meta-box');

      // Switch tab.
      $container.find('.tp-meta-tab').removeClass('active');
      $tab.addClass('active');

      // Switch panel.
      $container.find('.tp-meta-panel').removeClass('active');
      $container.find('.tp-meta-panel[data-panel="' + target + '"]').addClass('active');

      // Save active tab to localStorage.
      const postId = $container.data('post-id') || 'new';
      localStorage.setItem('tp_active_tab_' + postId, target);
    });

    // Restore last active tab.
    $('.tp-meta-box').each(function () {
      const $box = $(this);
      const postId = $box.data('post-id') || 'new';
      const saved = localStorage.getItem('tp_active_tab_' + postId);
      if (saved) {
        const $tab = $box.find('.tp-meta-tab[data-tab="' + saved + '"]');
        if ($tab.length) {
          $tab.trigger('click');
        }
      }
    });
  }

  /**
   * Media Upload Button (single image)
   */
  function initMediaUploads() {
    $(document).on('click', '.tp-media-upload-btn', function (e) {
      e.preventDefault();
      const $btn = $(this);
      const $field = $btn.closest('.tp-media-upload');
      const $input = $field.find('input[type="hidden"]');
      const $preview = $field.find('.tp-media-preview');

      const frame = wp.media({
        title: 'Select Image',
        button: { text: 'Use This Image' },
        multiple: false,
      });

      frame.on('select', function () {
        const attachment = frame.state().get('selection').first().toJSON();
        $input.val(attachment.id);
        $preview.html('<img src="' + (attachment.sizes?.thumbnail?.url || attachment.url) + '" alt="">');
      });

      frame.open();
    });

    $(document).on('click', '.tp-media-remove-btn', function (e) {
      e.preventDefault();
      const $field = $(this).closest('.tp-media-upload');
      $field.find('input[type="hidden"]').val('');
      $field.find('.tp-media-preview').html('');
    });
  }

  /**
   * Gallery Field (multiple images) — NewPropertyz-style
   */
  function initGalleryFields() {
    // Open media frame to choose multiple images
    $(document).on('click', '.tp-gallery-add', function (e) {
      e.preventDefault();
      const $field = $(this).closest('.tp-gallery-field');
      const $input = $field.find('.tp-gallery-ids');
      const $grid = $field.find('.tp-gallery-grid');

      const frame = wp.media({
        title: 'Choose Media',
        button: { text: 'Add to Gallery' },
        multiple: true,
        library: { type: 'image' },
      });

      frame.on('select', function () {
        const selection = frame.state().get('selection');
        const existingIds = $input.val() ? $input.val().split(',').filter(Boolean) : [];

        selection.forEach(function (attachment) {
          const att = attachment.toJSON();
          // Don't add duplicates
          if (existingIds.indexOf(String(att.id)) === -1) {
            existingIds.push(String(att.id));
            const thumbUrl = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
            $grid.append(
              '<div class="tp-gallery-item" data-id="' + att.id + '">' +
              '<img src="' + thumbUrl + '" alt="" />' +
              '<button type="button" class="tp-gallery-remove" title="Remove">&times;</button>' +
              '</div>'
            );
          }
        });

        $input.val(existingIds.join(','));
      });

      frame.open();
    });

    // Remove single image from gallery
    $(document).on('click', '.tp-gallery-remove', function (e) {
      e.preventDefault();
      const $item = $(this).closest('.tp-gallery-item');
      const $field = $item.closest('.tp-gallery-field');
      const $input = $field.find('.tp-gallery-ids');
      const removeId = String($item.data('id'));

      $item.remove();

      // Update hidden input
      const ids = $input.val().split(',').filter(function (id) {
        return id && id !== removeId;
      });
      $input.val(ids.join(','));
    });

    // Make gallery sortable (if jQuery UI available)
    if ($.fn.sortable) {
      $('.tp-gallery-grid').sortable({
        items: '.tp-gallery-item',
        cursor: 'move',
        tolerance: 'pointer',
        update: function () {
          const $grid = $(this);
          const $field = $grid.closest('.tp-gallery-field');
          const $input = $field.find('.tp-gallery-ids');
          const ids = [];
          $grid.find('.tp-gallery-item').each(function () {
            ids.push($(this).data('id'));
          });
          $input.val(ids.join(','));
        },
      });
    }
  }

  /**
   * Repeater / Configuration Rows
   */
  function initConfigTable() {
    $(document).on('click', '.tp-add-config-row', function (e) {
      e.preventDefault();
      const $table = $(this).closest('.tp-config-wrapper').find('.tp-config-table tbody');
      const $template = $table.find('tr:last').clone();
      $template.find('input, select').val('');
      $table.append($template);
    });

    $(document).on('click', '.tp-remove-config-row', function (e) {
      e.preventDefault();
      const $tbody = $(this).closest('tbody');
      if ($tbody.find('tr').length > 1) {
        $(this).closest('tr').remove();
      }
    });
  }

  /**
   * JSON Field Editor (for pros, cons, highlights, offers, etc.)
   */
  function initJsonFields() {
    $(document).on('click', '.tp-json-add', function (e) {
      e.preventDefault();
      const $list = $(this).siblings('.tp-json-list');
      const $item = $('<div class="tp-json-item"><input type="text" value="" /><button type="button" class="button tp-json-remove">&times;</button></div>');
      $list.append($item);
    });

    $(document).on('click', '.tp-json-remove', function (e) {
      e.preventDefault();
      $(this).closest('.tp-json-item').remove();
    });

    // Serialize JSON fields before form submit.
    $('form#post').on('submit', function () {
      $('.tp-json-field').each(function () {
        const $field = $(this);
        const $hidden = $field.find('input[type="hidden"].tp-json-value');
        const items = [];
        $field.find('.tp-json-list input[type="text"]').each(function () {
          const val = $(this).val().trim();
          if (val) items.push(val);
        });
        $hidden.val(JSON.stringify(items));
      });
    });
  }

  // Initialize on document ready.
  $(document).ready(function () {
    initMetaTabs();
    initMediaUploads();
    initGalleryFields();
    initConfigTable();
    initJsonFields();
  });

})(jQuery);
