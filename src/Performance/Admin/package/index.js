/**
 * Solid Performance Settings
 *
 */
import { createRoot } from '@wordpress/element';
import SolidPerformanceSettings from './settings.js';

const rootElement = document.querySelector('.solidwp-performance-settings-main');
if ( rootElement ) {
	createRoot( rootElement ).render( <SolidPerformanceSettings /> );
}