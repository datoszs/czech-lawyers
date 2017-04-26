import React from 'react';
import PropTypes from 'prop-types';
import {connect} from 'react-redux';
import {Button, Glyphicon} from 'react-bootstrap';
import {Msg} from '../containers';
import {getAdvocate} from './selectors';

const CakLinkComponent = ({href}) => (
    <Button href={href} disabled={!href} target="_blank"><Msg msg="cak.link" /> <Glyphicon glyph="new-window" /></Button>
);

CakLinkComponent.propTypes = {
    href: PropTypes.string,
};

CakLinkComponent.defaultProps = {
    href: null,
};

const mapStateToProps = (state) => {
    const advocate = getAdvocate(state);
    return {href: advocate ? advocate.remoteUrl : null};
};

export default connect(mapStateToProps)(CakLinkComponent);
