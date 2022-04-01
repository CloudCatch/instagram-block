/**
 * BLOCK: instagram-block
 *
 * Registering a basic block with Gutenberg.
 * Simple block, renders and saves the same content without any interactivity.
 */

//  Import CSS.
import './editor.scss';
import './style.scss';

import './view';

import metadata from './block.json';

import icon from './icon';
import { useEffect } from 'react';

const { name } = metadata;

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { PanelBody, RangeControl, ToggleControl, TextareaControl, ExternalLink } = wp.components;
const ServerSideRender = wp.serverSideRender;
const { useBlockProps, InspectorControls, InspectorAdvancedControls } = wp.blockEditor;

const settings = {
	icon: icon,
	edit: ( props ) => {
		const {
			attributes,
			setAttributes,
		} = props;

		const {
			columns,
			carousel,
			centerMode,
			items,
			infinite,
			autoWidth,
			autoHeight,
			carouselJson,
		} = attributes;

		const blockProps = useBlockProps();

		setInterval( () => {
			if ( typeof window.ccInstagram.carousel.init() !== 'undefined' ) {
				window.ccInstagram.carousel.init();
			}
		}, 1000 );

		return (
			<div>
				<InspectorAdvancedControls>
					{ carousel && (
						<TextareaControl
							label={ __( 'Carousel JSON options' ) }
							value={ carouselJson }
							onChange={ ( value ) => {
								setAttributes( { carouselJson: value } );
							} }
							help={
								<span>
									{ __( 'Use JSON to specify specific Tiny Slider options.' ) + ' ' }
									<ExternalLink href="https://github.com/ganlanyuan/tiny-slider#options">
										{ __( 'View options' ) }
									</ExternalLink>
								</span>
							}
						/>
					) }
				</InspectorAdvancedControls>
				<InspectorControls>
					<PanelBody>
						{ ! carousel && (
							<RangeControl
								label={ __( 'Columns' ) }
								value={ columns }
								onChange={ ( value ) => {
									setAttributes( { columns: value } );
								} }
								min={ 1 }
								max={ 8 }
							/>
						) }
						<ToggleControl
							label={ __( 'Enable carousel' ) }
							checked={ carousel }
							onChange={ () => {
								setAttributes( { carousel: ! carousel } );
							} }
						/>
						{ carousel && (
							<ToggleControl
								label={ __( 'Infinite loop' ) }
								checked={ infinite }
								onChange={ () => {
									setAttributes( { infinite: ! infinite } );
								} }
							/>
						) }
						{ carousel && (
							<ToggleControl
								label={ __( 'Center mode' ) }
								checked={ centerMode }
								onChange={ () => {
									setAttributes( { centerMode: ! centerMode } );
								} }
							/>
						) }
						{ carousel && (
							<ToggleControl
								label={ __( 'Auto width' ) }
								checked={ autoWidth }
								onChange={ () => {
									setAttributes( { autoWidth: ! autoWidth } );
								} }
							/>
						) }
						{ carousel && (
							<ToggleControl
								label={ __( 'Auto height' ) }
								checked={ autoHeight }
								onChange={ () => {
									setAttributes( { autoHeight: ! autoHeight } );
								} }
							/>
						) }
						{ carousel && (
							<RangeControl
								label={ __( 'Slides in viewport' ) }
								value={ items }
								onChange={ ( value ) => {
									setAttributes( { items: value } );
								} }
								min={ 1 }
								max={ 8 }
							/>
						) }
					</PanelBody>
				</InspectorControls>
				<div { ...blockProps }>
					<ServerSideRender
						block="cloudcatch/instagram"
						attributes={ attributes }
					/>
				</div>
			</div>
		);
	},
};

registerBlockType( { name, ...metadata }, settings );
