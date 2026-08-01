/**
 * 10Projects Admin Scripts
 *
 * Handles tabbed meta boxes, media uploads, repeater fields.
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
   * Media Upload Button
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
   * JSON Field Editor (for pros, cons, highlights, etc.)
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
    initConfigTable();
    initJsonFields();
  });

})(jQuery);
