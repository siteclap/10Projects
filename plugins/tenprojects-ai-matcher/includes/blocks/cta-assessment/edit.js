/**
 * Assessment CTA block — Editor view.
 *
 * @package TenProjects
 * @since 1.0.0
 */
( function () {
    var el                = wp.element.createElement;
    var useBlockProps     = wp.blockEditor.useBlockProps;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var RichText          = wp.blockEditor.RichText;
    var PanelBody         = wp.components.PanelBody;
    var SelectControl     = wp.components.SelectControl;
    var TextControl       = wp.components.TextControl;
    var registerBlockType = wp.blocks.registerBlockType;

    /**
     * Get variant styles for the editor preview.
     */
    function getVariantStyles( variant ) {
        if ( variant === 'gradient' ) {
            return {
                background: 'linear-gradient(135deg, #7c3aed 0%, #2563eb 100%)',
                color: '#ffffff',
                padding: '48px 32px',
                borderRadius: '16px',
                textAlign: 'center',
            };
        }
        if ( variant === 'inline' ) {
            return {
                background: '#f1f5f9',
                color: '#1e293b',
                padding: '24px 32px',
                borderRadius: '12px',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'space-between',
                gap: '16px',
            };
        }
        // simple
        return {
            background: '#ffffff',
            color: '#1e293b',
            padding: '48px 32px',
            borderRadius: '16px',
            border: '1px solid #e2e8f0',
            textAlign: 'center',
        };
    }

    registerBlockType( 'tenprojects/cta-assessment', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var variant       = attributes.variant || 'gradient';
            var isInline      = variant === 'inline';

            var blockProps = useBlockProps( {
                style: getVariantStyles( variant ),
            } );

            return el( 'div', blockProps,
                // Inspector sidebar controls.
                el( InspectorControls, null,
                    el( PanelBody, { title: 'CTA Settings', initialOpen: true },
                        el( SelectControl, {
                            label: 'Variant',
                            value: variant,
                            options: [
                                { label: 'Gradient', value: 'gradient' },
                                { label: 'Simple', value: 'simple' },
                                { label: 'Inline', value: 'inline' },
                            ],
                            onChange: function ( val ) {
                                setAttributes( { variant: val } );
                            },
                        } ),
                        el( TextControl, {
                            label: 'Button Text',
                            value: attributes.button_text || 'Find My Top 10',
                            onChange: function ( val ) {
                                setAttributes( { button_text: val } );
                            },
                        } )
                    )
                ),

                // Content area.
                el( 'div', {
                    style: isInline ? { flex: '1' } : {},
                },
                    el( RichText, {
                        tagName: 'h2',
                        value: attributes.heading || '',
                        onChange: function ( val ) {
                            setAttributes( { heading: val } );
                        },
                        placeholder: 'Find Your Perfect Home',
                        style: {
                            fontSize: isInline ? '20px' : '28px',
                            fontWeight: '700',
                            margin: '0 0 8px',
                            color: 'inherit',
                        },
                    } ),
                    el( RichText, {
                        tagName: 'p',
                        value: attributes.subheading || '',
                        onChange: function ( val ) {
                            setAttributes( { subheading: val } );
                        },
                        placeholder: 'Add a subheading...',
                        style: {
                            fontSize: isInline ? '14px' : '16px',
                            margin: '0',
                            opacity: 0.9,
                            color: 'inherit',
                        },
                    } )
                ),

                // Button preview.
                el( 'div', {
                    style: {
                        marginTop: isInline ? '0' : '24px',
                    },
                },
                    el( 'span', {
                        style: {
                            display: 'inline-block',
                            padding: isInline ? '10px 20px' : '14px 32px',
                            backgroundColor: variant === 'gradient' ? 'rgba(255,255,255,0.2)' : '#7c3aed',
                            color: '#ffffff',
                            borderRadius: '8px',
                            fontWeight: '600',
                            fontSize: isInline ? '14px' : '16px',
                            cursor: 'default',
                        },
                    }, attributes.button_text || 'Find My Top 10' )
                )
            );
        },
    } );
} )();
