import React from 'react';
import PropTypes from 'prop-types';
import {Panel} from 'react-bootstrap';
import PanelBody from './PanelBody';

const DetailPanel = ({children, footer, title, onClick}) => (
    <Panel
        bsStyle="default"
        className="detail-panel"
        footer={footer}
        onClick={onClick}
    >
        <PanelBody>
            <h2 className="title">{title}</h2>
            {children}
        </PanelBody>
    </Panel>
);

DetailPanel.propTypes = {
    children: PropTypes.node,
    footer: PropTypes.node,
    title: PropTypes.node.isRequired,
    onClick: PropTypes.func,
};

DetailPanel.defaultProps = {
    children: null,
    footer: null,
    onClick: () => {}, // do nothing
};

export default DetailPanel;
