import React, {PropTypes} from 'react';
import {connect} from 'react-redux';
import {List} from 'immutable';
import {Panel} from 'react-bootstrap';
import {Msg} from '../containers';
import {getDocuments} from './selectors';
import DocumentDetail from './DocumentDetail';

const DocumentContainer = ({documents}) => (
    <section>
        <h1><Panel><Msg msg="case.documents" /></Panel></h1>
        {documents.map((document) => <DocumentDetail key={document} id={document} />)}
    </section>
);

DocumentContainer.propTypes = {
    documents: PropTypes.instanceOf(List).isRequired,
};

const mapStateToProps = (state) => ({
    documents: getDocuments(state),
});

export default connect(mapStateToProps)(DocumentContainer);
