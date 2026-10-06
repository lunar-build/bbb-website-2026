# Slack deployment notifications

# Usage

Clone this repo into `trellis/roles` and remove the git directory to prevent submodule creation.
```bash
cd trellis/roles && git clone git@github.com:lunar-build/slack-deployment-notification.git && rm -rf slack-deployment-notification/.git
```

Include in `trellis/roles/deploy/hooks/finalize-after.yml`.

```yaml
- name: Send deployment notification
  include_role:
    name: slack-deployment-notification
```