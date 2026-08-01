/**
 * Project Card block — Editor view.
 *
 * @package TenProjects
 * @since 1.0.0
 */
( function () {
    var el                = wp.element.createElement;
    var useBlockProps     = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody         = wp.components.PanelBody;
    var TextControl       = wp.components.TextControl;
    var ToggleControl     = wp.components.ToggleControl;
    var registerBlockType = wp.blocks.registerBlockType;

    registerBlockType( 'tenprojects/project-card', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps    = useBlockProps( {
                style: {
                    padding: '24px',
                    backgroundColor: '#ffffff',
                    border: '1px solid #e2e8f0',
                    borderRadius: '12px',
                },
            } );

            var projectId = attributes.project_id || 0;

            return el( 'div', blockProps,
                // Inspector sidebar controls.
                el( InspectorControls, null,
                    el( PanelBody, { title: 'Project Settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Project ID',
                            help: 'Enter the WordPress post ID of the project.',
                            value: projectId ? String( projectId ) : '',
                            onChange: function ( val ) {
                                setAttributes( { project_id: parseInt( val, 10 ) || 0 } );
                            },
                        } ),
                        el( ToggleControl, {
                            label: 'Show Fit Score',
                            checked: attributes.show_score,
                            onChange: function ( val ) {
                                setAttributes( { show_score: val } );
                            },
                        } ),
                        attributes.show_score && el( TextControl, {
                            label: 'Fit Score (0-100)',
                            type: 'number',
                            value: attributes.score ? String( attributes.score ) : '0',
                            onChange: function ( val ) {
                                var num = parseInt( val, 10 ) || 0;
                                setAttributes( { score: Math.min( 100, Math.max( 0, num ) ) } );
                            },
                        } )
                    )
                ),

                // Block preview.
                el( 'div', { style: { textAlign: 'center' } },
                    el( 'div', {
                        style: {
                            fontSize: '28px',
                            marginBottom: '8px',
                        },
                    }, '\uD83C\uDFE0' ),
                    el( 'h4', {
                        style: {
                            margin: '0 0 4px',
                            fontSize: '16px',
                            fontWeight: '600',
                            color: '#1e293b',
                        },
                    }, 'Project Card' ),
                    el( 'p', {
                        style: {
                            margin: '0',
                            fontSize: '13px',
                            color: '#64748b',
                        },
                    }, projectId
                        ? 'Project ID: ' + projectId + ( attributes.show_score ? ' | Score: ' + attributes.score : '' )
                        : 'Set a Project ID in the sidebar to display a project card.'
                    )
                )
            );
        },
    } );
} )();
