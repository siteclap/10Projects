/**
 * Fit Score Badge block — Editor view.
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
    var SelectControl     = wp.components.SelectControl;
    var registerBlockType = wp.blocks.registerBlockType;

    /**
     * Get badge colour based on score.
     */
    function getBadgeColor( score ) {
        if ( score >= 80 ) return '#f59e0b';
        if ( score >= 70 ) return '#22c55e';
        if ( score >= 60 ) return '#3b82f6';
        return '#94a3b8';
    }

    /**
     * Get badge font size based on size attribute.
     */
    function getBadgeFontSize( size ) {
        if ( size === 'large' ) return '32px';
        if ( size === 'small' ) return '18px';
        return '24px';
    }

    registerBlockType( 'tenprojects/fit-score', {
        edit: function ( props ) {
            var attributes    = props.attributes;
            var setAttributes = props.setAttributes;
            var score         = attributes.score || 0;
            var size          = attributes.size || 'medium';

            var blockProps = useBlockProps( {
                style: { textAlign: 'center', padding: '16px' },
            } );

            return el( 'div', blockProps,
                // Inspector sidebar controls.
                el( InspectorControls, null,
                    el( PanelBody, { title: 'Score Settings', initialOpen: true },
                        el( TextControl, {
                            label: 'Score (0-100)',
                            type: 'number',
                            value: String( score ),
                            onChange: function ( val ) {
                                var num = parseInt( val, 10 ) || 0;
                                setAttributes( { score: Math.min( 100, Math.max( 0, num ) ) } );
                            },
                        } ),
                        el( SelectControl, {
                            label: 'Size',
                            value: size,
                            options: [
                                { label: 'Small', value: 'small' },
                                { label: 'Medium', value: 'medium' },
                                { label: 'Large', value: 'large' },
                            ],
                            onChange: function ( val ) {
                                setAttributes( { size: val } );
                            },
                        } )
                    )
                ),

                // Badge preview.
                el( 'div', {
                    style: {
                        display: 'inline-flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        width: size === 'large' ? '80px' : size === 'small' ? '44px' : '60px',
                        height: size === 'large' ? '80px' : size === 'small' ? '44px' : '60px',
                        borderRadius: '50%',
                        backgroundColor: getBadgeColor( score ),
                        color: '#ffffff',
                    },
                },
                    el( 'span', {
                        style: {
                            fontSize: getBadgeFontSize( size ),
                            fontWeight: '700',
                            lineHeight: '1',
                        },
                    }, score > 0 ? String( score ) : '--' ),
                    el( 'span', {
                        style: {
                            fontSize: size === 'large' ? '12px' : '10px',
                            fontWeight: '500',
                            lineHeight: '1',
                            marginTop: '2px',
                        },
                    }, 'Fit' )
                )
            );
        },
    } );
} )();
