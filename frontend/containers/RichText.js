import PropTypes from 'prop-types';
import ReactMarkdown from 'react-markdown';
import {connect} from 'react-redux';
import translate from '../translate';
import {TextNode} from '../components/markdown';
import MarkdownLink from './MarkdownLink';

const mapStateToProps = (state, {msg, ...params}) => ({
    source: translate.getMessage(state, msg, params),
});

const mapDispatchToProps = () => ({
    renderers: {
        Link: MarkdownLink,
        Text: TextNode,
    },
    escapeHtml: true,
    containerTagName: 'div',
});

const RichText = connect(mapStateToProps, mapDispatchToProps)(ReactMarkdown);

RichText.propTypes = {
    msg: PropTypes.string.isRequired,
};

export default RichText;
