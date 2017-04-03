import {PropTypes} from 'react';
import ReactMarkdown from 'react-markdown';
import {connect} from 'react-redux';
import translate from '../translate';
import RouterLink from './RouterLink';

const mapStateToProps = (state, {msg, params}) => ({
    source: translate.getMessage(state, msg, params),
});

const mapDispatchToProps = () => ({
    renderers: {
        Link: RouterLink,
    },
    escapeHtml: true,
});

const RichText = connect(mapStateToProps, mapDispatchToProps)(ReactMarkdown);

RichText.propTypes = {
    msg: PropTypes.string.isRequired,
    params: PropTypes.object, //eslint-disable-line react/forbid-prop-types
};

RichText.defaultProps = {
    params: null,
};

export default RichText;
