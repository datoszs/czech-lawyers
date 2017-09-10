import React from 'react';
import PropTypes from 'prop-types';
import {wrapEventStop} from '../util';
import PanelBody from './PanelBody';

const DetailPanel = ({children, footer, title, onClick, href}) => (
    <a
        className="panel panel-default detail-panel"
        onClick={wrapEventStop(onClick)}
        href={href}
    >
        <div className="panel-body">
            <PanelBody>
                <h2 className="title">{title}</h2>
                {children}
            </PanelBody>
        </div>
        <div className="panel-footer">{footer}</div>
    </a>
);

DetailPanel.propTypes = {
    children: PropTypes.node,
    footer: PropTypes.node,
    title: PropTypes.node.isRequired,
    onClick: PropTypes.func,
    href: PropTypes.string,
};

DetailPanel.defaultProps = {
    children: null,
    footer: null,
    onClick: () => {}, // do nothing
    href: '',
};

export default DetailPanel;
