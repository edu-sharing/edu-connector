{{- define "edusharing_connector.chart" -}}
{{- printf "%s-%s" .Chart.Name .Chart.Version | replace "+" "_" | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{- define "edusharing_connector.name" -}}
{{- default .Chart.Name .Values.nameOverride | trunc 63 | trimSuffix "-" -}}
{{- end -}}

{{- define "edusharing_connector.labels" -}}
{{ include "edusharing_connector.labels.instance" . }}
helm.sh/chart: {{ include "edusharing_connector.chart" . }}
app.kubernetes.io/managed-by: {{ .Release.Service }}
app.kubernetes.io/instance: {{ .Release.Name }}
{{- end -}}

{{- define "edusharing_connector.labels.instance" -}}
{{ include "edusharing_connector.labels.app" . }}
{{ include "edusharing_connector.labels.version" . }}
{{- end -}}

{{- define "edusharing_connector.labels.version" -}}
version: {{ .Chart.AppVersion | quote }}
app.kubernetes.io/version: {{ .Chart.AppVersion | quote }}
{{- end -}}

{{- define "edusharing_connector.labels.app" -}}
app: {{ include "edusharing_connector.name" . }}
app.kubernetes.io/name: {{ include "edusharing_connector.name" . }}
{{- end -}}
